<?php
namespace Netdudes\ImporterBundle\Importer\Configuration\Reader;

use Netdudes\ImporterBundle\Importer\Configuration\Collection\ConfigurationCollection;
use Netdudes\ImporterBundle\Importer\Configuration\EntityConfiguration;
use Netdudes\ImporterBundle\Importer\Configuration\Field\FieldConfigurationFactory;
use Netdudes\ImporterBundle\Importer\Configuration\Field\FieldConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\Field\LookupFieldConfiguration;
use Netdudes\ImporterBundle\Importer\Configuration\Reader\Exception\FieldConfigurationParseException;
use Netdudes\ImporterBundle\Importer\Configuration\Reader\Exception\MissingParameterException;
use Netdudes\ImporterBundle\Importer\Configuration\Reader\Exception\UndefinedConfigurationNodeTypeException;
use Netdudes\ImporterBundle\Importer\Configuration\RelationshipConfiguration;
use Netdudes\ImporterBundle\Importer\Configuration\UpdatingEntityConfiguration;
use Symfony\Component\Yaml\Parser;

class YamlConfigurationReader implements ConfigurationReaderInterface
{
    /**
     * @var Parser
     */
    private $yamlParser;

    /**
     * @var FieldConfigurationFactory
     */
    private $fieldConfigurationFactory;

    /**
     * @param Parser                    $yamlParser
     * @param FieldConfigurationFactory $fieldConfigurationFactory
     */
    public function __construct(Parser $yamlParser, FieldConfigurationFactory $fieldConfigurationFactory)
    {
        $this->yamlParser = $yamlParser;
        $this->fieldConfigurationFactory = $fieldConfigurationFactory;
    }

    /**
     * @param string $file
     *
     * @return ConfigurationCollection|null
     */
    public function readFile($file)
    {
        return $this->read(file_get_contents($file));
    }

    /**
     * @param string $yaml
     *
     * @return ConfigurationCollection|null
     */
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

        return $configurationCollection;
    }

    /**
     * @param array $parsedYamlArray
     *
     * @throws UndefinedConfigurationNodeTypeException
     *
     * @return EntityConfiguration
     */
    public function readParsedYamlArray(array $parsedYamlArray)
    {
        return $this->readConfigurationNode($parsedYamlArray);
    }

    /**
     * @param array $rootConfigurationNode
     *
     * @throws UndefinedConfigurationNodeTypeException
     *
     * @return EntityConfiguration
     */
    private function readConfigurationNode(array $rootConfigurationNode)
    {
        $type = $this->getChild($rootConfigurationNode, 'type');
        if (!is_null($type)) {
            switch ($type) {
                case ('update'):
                    return $this->readUpdateNode($rootConfigurationNode);
                case ('joinedImport'):
                    return $this->readJoinedImportNode($rootConfigurationNode);
            }
            throw new UndefinedConfigurationNodeTypeException("Unknown configuration node type $type");
        }

        return $this->readEntityNode($rootConfigurationNode);
    }

    /**
     * @param array  $node
     * @param string $childName
     *
     * @return null|string
     */
    private function getChild(array $node, $childName)
    {
        if ($this->hasChild($node, $childName)) {
            return $node[$childName];
        }

        return null;
    }

    /**
     * @param array  $node
     * @param string $childName
     *
     * @return bool
     */
    private function hasChild(array $node, $childName)
    {
        return array_key_exists($childName, $node);
    }

    /**
     * @param array $node
     *
     * @throws Exception\MissingParameterException
     *
     * @return EntityConfiguration
     */
    private function readEntityNode(array $node)
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

        $help = $this->getHelp($node);
        if (!is_null($help)) {
            $entityConfiguration->setHelp($help);
        }

        return $entityConfiguration;
    }

    /**
     * @param array $node
     *
     * @throws MissingParameterException
     *
     * @return UpdatingEntityConfiguration
     */
    protected function readUpdateNode(array $node)
    {
        $entityConfiguration = $this->readEntityNode($node);
        $updateEntityConfiguration = UpdatingEntityConfiguration::createFromEntityConfiguration($entityConfiguration);

        $updateMatchFields = $this->getChildOrThrowMissingParameterException($node, 'update_match_fields');
        $updateEntityConfiguration->setUpdateMatchFields($updateMatchFields);

        return $updateEntityConfiguration;
    }

    /**
     * @param array  $node
     * @param string $child
     *
     * @throws MissingParameterException
     *
     * @return string|null
     */
    private function getChildOrThrowMissingParameterException(array $node, $child)
    {
        $lookupField = $this->getChild($node, $child);
        if (is_null($lookupField)) {
            $exception = new MissingParameterException("Missing $child parameter in field configuration");
            $exception->setParameter($child);
            throw $exception;
        }

        return $lookupField;
    }

    /**
     * @param array $fieldConfigurationNode
     *
     * @throws FieldConfigurationParseException
     * @throws MissingParameterException
     *
     * @return FieldConfigurationInterface
     */
    private function readFieldConfigurationNode(array $fieldConfigurationNode)
    {
        // Pick up fields with type not matching any method, but with lookupProperty, as old-style lookup field configs.
        // TODO: Remove this functionality when no longer necessary
        if ($this->hasChild($fieldConfigurationNode, 'lookupProperty')) {
            return $this->readLegacyLookupFieldConfigurationNode($fieldConfigurationNode);
        }

        $fieldConfiguration = $this->fieldConfigurationFactory->create($fieldConfigurationNode);

        return $fieldConfiguration;
    }

    /**
     * @param array $node
     *
     * @throws MissingParameterException
     *
     * @return LookupFieldConfiguration
     *
     * @deprecated
     */
    private function readLegacyLookupFieldConfigurationNode(array $node)
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
     * @param array $node
     *
     * @throws FieldConfigurationParseException
     * @throws MissingParameterException
     * @throws \Exception
     *
     * @return RelationshipConfiguration
     */
    private function readJoinedImportNode(array $node)
    {
        $ownerClass = $this->getChildOrThrowMissingParameterException($node, 'owner');
        $fields = $this->getChildOrThrowMissingParameterException($node, 'columns');

        if (count($fields) !== 2) {
            throw new FieldConfigurationParseException('A joinedImport configuration node must have two columns');
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
                    throw new MissingParameterException('The related class column must describe the ownerCallback');
                }
                $joinedImportConfiguration->setAssignmentMethod($ownerCallback);
            }
        }

        if (is_null($joinedImportConfiguration->getOwnerLookupConfigurationField())) {
            throw new MissingParameterException('Missing column that matches the owner class in joined import');
        }

        return $joinedImportConfiguration;
    }

    /**
     * @param array $node
     *
     * @throws MissingParameterException
     *
     * @return LookupFieldConfiguration
     */
    private function readLookupConfigurationNode(array $node)
    {
        $fieldConfiguration = new LookupFieldConfiguration();
        $lookupProperty = $this->getChildOrThrowMissingParameterException($node, 'lookupProperty');
        $fieldConfiguration->setLookupField($lookupProperty);
        $entity = $this->getChildOrThrowMissingParameterException($node, 'entity');
        $fieldConfiguration->setClass($entity);
        $fieldConfiguration->setField($this->getChild($node, 'property'));

        return $fieldConfiguration;
    }

    /**
     * @param array $node
     *
     * @return null|string
     */
    private function getHelp(array $node)
    {
        return $this->getChild($node, 'help');
    }
}
