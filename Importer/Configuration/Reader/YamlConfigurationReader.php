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
    /**
     * @var \Symfony\Component\Yaml\Parser
     */
    private $yamlParser;

    public function __construct(Parser $yamlParser)
    {
        $this->yamlParser = $yamlParser;
    }

    public function readFile($file, $configurationKey = null)
    {
        return $this->read(file_get_contents($file));
    }

    public function read($yaml)
    {
        $parsedYamlArray = $this->yamlParser->parse($yaml);

        if (count($parsedYamlArray) == 0) {
            return null;
        }

        $configurationCollection = new ConfigurationCollection();

        foreach ($parsedYamlArray as $configurationKey => $configurationArray) {
            $configurationCollection->add($configurationKey, $this->readParsedYamlArray($configurationArray));
        }

        if (count($configurationCollection) == 1) {
            return $configurationCollection->get($configurationCollection->getConfigurationIds()[0]);
        }

        return $configurationCollection;
    }

    /**
     * @param $parsedYamlArray
     */
    public function readParsedYamlArray(array $parsedYamlArray)
    {
        return $this->readConfigurationNode($parsedYamlArray);
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
        $entity = $this->getChildOrThrowMissingParameterException($node, 'entity');
        $fields = $this->getChildOrThrowMissingParameterException($node, 'columns');

        $fieldConfigurations = [];
        foreach ($fields as $fieldName => $field) {
            $fieldConfigurations[$fieldName] = $this->readFieldConfigurationNode($field);
        }

        $entityConfiguration = new EntityConfiguration();
        $entityConfiguration->setClass($entity);
        $entityConfiguration->setFields($fieldConfigurations);

        return $entityConfiguration;
    }

    /**
     * @param array $node
     *
     * @param       $child
     *
     * @throws Exception\MissingParameterException
     * @return mixed
     */
    protected function getChildOrThrowMissingParameterException(array $node, $child)
    {
        if (!($lookupField = $this->getChild($node, $child))) {
            $exception = new MissingParameterException("Missing $child parameter in field configuration");
            $exception->setParameter($child);
            throw $exception;
        }
        return $lookupField;
    }

    protected function readFieldConfigurationNode(array $fieldConfigurationNode)
    {
        $property = $this->getChildOrThrowMissingParameterException($fieldConfigurationNode, 'property');

        if (!($type = $this->getChild($fieldConfigurationNode, 'type'))) {
            $fieldConfiguration = new LiteralFieldConfiguration();
            $fieldConfiguration->setField($property);

            return $fieldConfiguration;
        }

        $expectedReadFieldMethod = 'read' . ucfirst($type) . 'FieldConfigurationNode';
        if (method_exists($this, $expectedReadFieldMethod)) {
            return $this->{$expectedReadFieldMethod}($fieldConfigurationNode);
        }

        // Pick up fields with type not matching any method, but with lookupProperty, as old-style lookup field configs.
        // TODO: Remove this functionality when no longer necessary
        if ($this->hasChild($fieldConfigurationNode, 'lookupProperty')) {
            return $this->readLegacyLookupFieldConfigurationNode($fieldConfigurationNode);
        }

        $prettyPrintNode = print_r($fieldConfigurationNode, true);
        throw new FieldConfigurationParseException("Could not identify the type of field:\n{$prettyPrintNode}");

    }

    protected function readLegacyLookupFieldConfigurationNode(array $node)
    {
        $fieldConfiguration = new LookupFieldConfiguration();
        $lookupProperty = $this->getChildOrThrowMissingParameterException($node, 'lookupProperty');
        $class = $this->getChildOrThrowMissingParameterException($node, 'type');
        $fieldConfiguration->setLookupField($lookupProperty);
        $fieldConfiguration->setClass($class);
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
        $ownerClass = $this->getChildOrThrowMissingParameterException($node, 'owner');
        $fields = $this->getChildOrThrowMissingParameterException($node, 'columns');

        if (count($fields) !== 2) {
            throw new FieldConfigurationParseException("A joinedImport configuration node must have two columns");
        }

        $joinedImportConfiguration = new RelationshipConfiguration();
        foreach ($fields as $fieldName => $field) {
            try {
                $lookupFieldConfiguration = $this->readLookupConfigurationNode($field);
            } catch (MissingParameterException $exception) {
                if (!($exception->getParameter() === 'entity')) {
                    throw $exception;
                }

                // Finally, try to run it through the legacy reader
                // TODO: Remove this functionality when no longer necessary
                $lookupFieldConfiguration = $this->readLegacyLookupFieldConfigurationNode($field);
            }
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

    protected function readLookupConfigurationNode(array $node)
    {
        $fieldConfiguration = new LookupFieldConfiguration();
        $lookupProperty = $this->getChildOrThrowMissingParameterException($node, 'lookupProperty');
        $fieldConfiguration->setLookupField($lookupProperty);
        $entity = $this->getChildOrThrowMissingParameterException($node, 'entity');
        $fieldConfiguration->setClass($entity);
        $fieldConfiguration->setField($this->getChild($node, 'property'));

        return $fieldConfiguration;
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
