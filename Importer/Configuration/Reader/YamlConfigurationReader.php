<?php

namespace Netdudes\ImporterBundle\Importer\Configuration\Reader;

use Netdudes\ImporterBundle\Importer\Configuration\Collection\ConfigurationCollection;
use Netdudes\ImporterBundle\Importer\Configuration\Collection\ConfigurationCollectionInterface;
use Netdudes\ImporterBundle\Importer\Configuration\Field\DateFieldConfiguration;
use Netdudes\ImporterBundle\Importer\Configuration\Field\FileFieldConfiguration;
use Netdudes\ImporterBundle\Importer\Configuration\RelationshipConfiguration;
use Netdudes\ImporterBundle\Importer\Configuration\ConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\EntityConfiguration;
use Netdudes\ImporterBundle\Importer\Configuration\Field\DateTimeFieldConfiguration;
use Netdudes\ImporterBundle\Importer\Configuration\Field\LiteralFieldConfiguration;
use Netdudes\ImporterBundle\Importer\Configuration\Field\LookupFieldConfiguration;
use Netdudes\ImporterBundle\Importer\Configuration\Reader\Exception\FieldConfigurationParseException;
use Netdudes\ImporterBundle\Importer\Configuration\Reader\Exception\MissingParameterException;
use Netdudes\ImporterBundle\Importer\Configuration\Reader\Exception\UndefinedConfigurationNodeTypeException;
use Symfony\Component\Intl\Exception\NotImplementedException;
use Symfony\Component\Yaml\Parser;

class YamlConfigurationReader extends AbstractConfigurationReader
{
    protected $configurationCollection;

    function __construct()
    {
        $this->configurationCollection = new ConfigurationCollection();
    }

    public function readFile($file)
    {
        $parser = new Parser();
        $raw = $parser->parse(file_get_contents($file));

        $this->read($raw);
    }

    /**
     * @param $raw
     */
    public function read(array $raw)
    {
        $configurationCollection = new ConfigurationCollection();
        foreach ($raw as $entryName => $configurationNode) {
            $configurationCollection->add($entryName, $this->readConfigurationNode($configurationNode));
        }

        $this->configurationCollection = $configurationCollection;
    }

    protected function readConfigurationNode(array $node)
    {
        if ($type = $this->getChild($node, 'type')) {
            switch ($type) {
                case 'joinedImport':
                    return $this->readJoinedImportNode($node);
            }
            throw new UndefinedConfigurationNodeTypeException("Unknown configuration node type $type");
        }

        if (!($entity = $this->getChild($node, 'entity'))) {
            throw new MissingParameterException("Missing entity parameter in configuration node");
        }

        if (!($fields = $this->getChild($node, 'columns'))) {
            throw new MissingParameterException("Missing columns array in configuration node");
        }

        $fieldConfigurations = [];
        foreach ($fields as $fieldName => $field) {
            $fieldConfigurations[$fieldName] = $this->readFieldConfiguration($field);
        }

        $entityConfiguration = new EntityConfiguration();
        $entityConfiguration->setClass($entity);
        $entityConfiguration->setFields($fieldConfigurations);

        return $entityConfiguration;
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

    private function readJoinedImportNode(array $node)
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
            $lookupFieldConfiguration = $this->readLookupFieldConfiguration($field);
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

    protected function readLookupFieldConfiguration(array $node)
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

    protected function readFieldConfiguration(array $node)
    {
        if (!($property = $this->getChild($node, 'property'))) {
            throw new MissingParameterException("Missing property in field definition");
        }

        if (!($type = $this->getChild($node, 'type'))) {
            $fieldConfiguration = new LiteralFieldConfiguration();
            $fieldConfiguration->setField($property);
            return $fieldConfiguration;
        }

        if ($type == 'datetime') {
            return $this->readDatetimeFieldConfiguration($node);
        }

        if ($type == 'date') {
            return $this->readDateFieldConfiguration($node);
        }

        if ($type == 'file') {
            return $this->readFileFieldConfiguration($node);
        }

        if ($this->hasChild($node, 'lookupProperty')) {
            return $this->readLookupFieldConfiguration($node);
        }

        $prettyPrintNode = print_r($node, true);
        throw new FieldConfigurationParseException("Could not identify the type of field:\n{$prettyPrintNode}");

    }

    protected function readDatetimeFieldConfiguration(array $node)
    {
        $fieldConfiguration = new DateTimeFieldConfiguration();
        if ($format = $this->getChild($node, 'format')) {
            $fieldConfiguration->setFormat($format);
        }
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

    private function readDateFieldConfiguration($node)
    {
        $fieldConfiguration = new DateFieldConfiguration();
        if ($format = $this->getChild($node, 'format')) {
            $fieldConfiguration->setFormat($format);
        }
        $fieldConfiguration->setField($this->getChild($node, 'property'));
        return $fieldConfiguration;
    }

    private function readFileFieldConfiguration($node)
    {
        $fieldConfiguration = new FileFieldConfiguration();
        $fieldConfiguration->setField($this->getChild($node, 'property'));
        return $fieldConfiguration;
    }
}