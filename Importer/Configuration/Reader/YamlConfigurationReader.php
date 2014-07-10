<?php

namespace Netdudes\ImporterBundle\Importer\Configuration\Reader;

use Netdudes\ImporterBundle\Importer\Configuration\Collection\ConfigurationCollection;
use Netdudes\ImporterBundle\Importer\Configuration\Collection\ConfigurationCollectionInterface;
use Netdudes\ImporterBundle\Importer\Configuration\Field\DateFieldConfiguration;
use Netdudes\ImporterBundle\Importer\Configuration\Field\FileFieldConfiguration;
use Netdudes\ImporterBundle\Importer\Configuration\RelationshipConfiguration;
use Netdudes\ImporterBundle\Importer\Configuration\EntityConfiguration;
use Netdudes\ImporterBundle\Importer\Configuration\Field\DateTimeFieldConfiguration;
use Netdudes\ImporterBundle\Importer\Configuration\Field\LiteralFieldConfiguration;
use Netdudes\ImporterBundle\Importer\Configuration\Field\LookupFieldConfiguration;
use Netdudes\ImporterBundle\Importer\Configuration\Reader\Exception\FieldConfigurationParseException;
use Netdudes\ImporterBundle\Importer\Configuration\Reader\Exception\MissingParameterException;
use Netdudes\ImporterBundle\Importer\Configuration\Reader\Exception\UndefinedConfigurationNodeTypeException;
use Symfony\Component\Yaml\Parser;

class YamlConfigurationReader implements ConfigurationReaderInterface
{
    protected $configurationCollection;

    public function __construct()
    {
        $this->configurationCollection = new ConfigurationCollection();
    }

    public function readFile($file)
    {
        $this->read(file_get_contents($file));
    }

    public function read($yaml)
    {
        $parser = new Parser();
        $parsedYamlArray = $parser->parse($yaml);

        $this->readParsedYamlArray($parsedYamlArray);
    }

    /**
     * @param $parsedYamlArray
     */
    public function readParsedYamlArray(array $parsedYamlArray)
    {
        $configurationCollection = new ConfigurationCollection();
        foreach ($parsedYamlArray as $entryName => $configurationNode) {
            $configurationCollection->add($entryName, $this->readConfigurationNode($configurationNode));
        }

        $this->configurationCollection = $configurationCollection;
    }

    protected function readConfigurationNode(array $rootConfigurationNode)
    {
        if ($type = $this->getChild($rootConfigurationNode, 'type')) {
            $expectedReaderMethod = 'read' . ucfirst($type) . 'Node';
            if (method_exists($this, $expectedReaderMethod)) {
                return $this->{$expectedReaderMethod}($rootConfigurationNode);
            }
            throw new UndefinedConfigurationNodeTypeException("Unknown configuration node type $type");
        }

        return $this->readEntityNode($rootConfigurationNode);
    }

    protected function getChild($node, $childName)
    {
        if ($this->hasChild($node, $childName)) {
            return $node[$childName];
        }
    }

    protected function hasChild(array $node, $childName)
    {
        return array_key_exists($childName, $node);
    }

    /**
     * @param array $node
     *
     * @return EntityConfiguration
     * @throws Exception\MissingParameterException
     */
    protected function readEntityNode(array $node)
    {
        if (!($entity = $this->getChild($node, 'entity'))) {
            throw new MissingParameterException("Missing entity parameter in configuration node");
        }

        if (!($fields = $this->getChild($node, 'columns'))) {
            throw new MissingParameterException("Missing columns array in configuration node");
        }

        $fieldConfigurations = [];
        foreach ($fields as $fieldName => $field) {
            $fieldConfigurations[$fieldName] = $this->readFieldConfigurationNode($field);
        }

        $entityConfiguration = new EntityConfiguration();
        $entityConfiguration->setClass($entity);
        $entityConfiguration->setFields($fieldConfigurations);

        return $entityConfiguration;
    }

    protected function readFieldConfigurationNode(array $fieldConfigurationNode)
    {
        if (!($property = $this->getChild($fieldConfigurationNode, 'property'))) {
            throw new MissingParameterException("Missing property in field definition");
        }

        if (!($type = $this->getChild($fieldConfigurationNode, 'type'))) {
            $fieldConfiguration = new LiteralFieldConfiguration();
            $fieldConfiguration->setField($property);

            return $fieldConfiguration;
        }

        if ($this->hasChild($fieldConfigurationNode, 'lookupProperty')) {
            return $this->readLookupFieldConfigurationNode($fieldConfigurationNode);
        }

        $expectedReadFieldMethod = 'read' . ucfirst($type) . 'FieldConfigurationNode';
        if (method_exists($this, $expectedReadFieldMethod)) {
            return $this->{$expectedReadFieldMethod}($fieldConfigurationNode);
        }

        $prettyPrintNode = print_r($fieldConfigurationNode, true);
        throw new FieldConfigurationParseException("Could not identify the type of field:\n{$prettyPrintNode}");

    }

    protected function readLookupFieldConfigurationNode(array $node)
    {
        $fieldConfiguration = new LookupFieldConfiguration();
        if (!($lookupField = $this->getChild($node, 'lookupProperty'))) {
            throw new MissingParameterException("Missing lookupProperty parameter in lookup field configuration");
        }
        $fieldConfiguration->setLookupField($lookupField);
        $fieldConfiguration->setClass($this->getChild($node, 'type'));
        $fieldConfiguration->setField($this->getChild($node, 'property'));

        return $fieldConfiguration;
    }

    /**
     * @return ConfigurationCollectionInterface
     */
    public function getConfigurationCollection()
    {
        return $this->configurationCollection;
    }

    protected function readJoinedImportNode(array $node)
    {
        if (!($ownerClass = $this->getChild($node, 'owner'))) {
            throw new MissingParameterException("Missing owner node in joined import");
        }

        if (!($fields = $this->getChild($node, 'columns'))) {
            throw new MissingParameterException("Missing columns node in joined import");
        }

        if (count($fields) !== 2) {
            throw new FieldConfigurationParseException("A joinedImport configuration node must have two columns");
        }

        $joinedImportConfiguration = new RelationshipConfiguration();
        foreach ($fields as $fieldName => $field) {
            $lookupFieldConfiguration = $this->readLookupFieldConfigurationNode($field);
            if ($lookupFieldConfiguration->getClass() == $ownerClass) {
                $joinedImportConfiguration->setOwnerLookupConfigurationField($lookupFieldConfiguration);
                $joinedImportConfiguration->setOwnerLookupFieldName($fieldName);
            } else {
                $joinedImportConfiguration->setRelatedLookupConfigurationField($lookupFieldConfiguration);
                $joinedImportConfiguration->setRelatedLookupFieldName($fieldName);
                if (!($ownerCallback = $this->getChild($this->getChild($fields, $fieldName), 'ownerCallback'))) {
                    throw new MissingParameterException("The related class column must describe the ownerCallback");
                }
                $joinedImportConfiguration->setAssignementMethod($ownerCallback);
            }
        }

        if (is_null($joinedImportConfiguration->getOwnerLookupConfigurationField())) {
            throw new MissingParameterException("Missing column that matches the owner class in joined import");
        }

        return $joinedImportConfiguration;

    }

    protected function readDatetimeFieldConfigurationNode(array $node)
    {
        $fieldConfiguration = new DateTimeFieldConfiguration();
        if ($format = $this->getChild($node, 'format')) {
            $fieldConfiguration->setFormat($format);
        }
        $fieldConfiguration->setField($this->getChild($node, 'property'));

        return $fieldConfiguration;
    }

    protected function readDateFieldConfigurationNode($node)
    {
        $fieldConfiguration = new DateFieldConfiguration();
        if ($format = $this->getChild($node, 'format')) {
            $fieldConfiguration->setFormat($format);
        }
        $fieldConfiguration->setField($this->getChild($node, 'property'));

        return $fieldConfiguration;
    }

    protected function readFileFieldConfigurationNode($node)
    {
        $fieldConfiguration = new FileFieldConfiguration();
        $fieldConfiguration->setField($this->getChild($node, 'property'));

        return $fieldConfiguration;
    }
}
