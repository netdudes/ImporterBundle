<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter;

use Doctrine\ORM\EntityManager;
use Netdudes\ImporterBundle\Importer\Configuration\ConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\EntityConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\Field\DateTimeFieldConfiguration;
use Netdudes\ImporterBundle\Importer\Configuration\Field\FieldConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\Field\LiteralFieldConfiguration;
use Netdudes\ImporterBundle\Importer\Configuration\Field\LookupFieldConfiguration;
use Netdudes\ImporterBundle\Importer\Interpreter\Field\DatetimeFieldInterpreter;
use Netdudes\ImporterBundle\Importer\Interpreter\Field\LiteralFieldInterpreter;
use Netdudes\ImporterBundle\Importer\Interpreter\Field\LookupFieldInterpreter;

class EntityDataInterpreter
{
    /**
     * @var \Netdudes\ImporterBundle\Importer\Configuration\EntityConfigurationInterface
     */
    protected $configuration;

    protected $affectedEntities;

    function __construct(EntityConfigurationInterface $configuration, EntityManager $entityManager)
    {
        $this->configuration = $configuration;
        $this->literalFieldInterpreter = new LiteralFieldInterpreter();
        $this->lookupFieldInterpreter = new LookupFieldInterpreter($entityManager);
        $this->datetimeFieldInterpreter = new DatetimeFieldInterpreter();
    }

    public function interpret($data, $associative = true)
    {
        $class = $this->configuration->getClass();
        foreach ($data as $row) {
            $entity = new $class;
            $interpretedData = $this->interpretRow($row, $associative);
            $this->injectInterpretedDataIntoEntity($entity, $interpretedData);
            $this->affectedEntities[] = $entity;
        }

        return $this->affectedEntities;
    }

    protected function interpretRow(array $row, $associative = true)
    {
        return $associative ? $this->interpretAssociativeRow($row) : $this->interpretOrderedRow($row);
    }

    protected function interpretAssociativeRow($columns)
    {
        $interpretedRow = [];
        foreach ($columns as $fieldName => $value) {
            $fieldConfiguration = $this->configuration->getField($fieldName);
            $interpretedRow[$fieldConfiguration->getField()] = $this->interpretField($fieldConfiguration, $value);
        }

        return $interpretedRow;

    }

    protected function interpretOrderedRow($row)
    {
        $interpretedRow = [];
        $orderedFields = array_keys($this->configuration->getFields());
        if (count($orderedFields) !== count($row)) {
            throw new \Exception("Number of columns in data differs from number of fields");
        }

        /** @var $fieldConfiguration FieldConfigurationInterface */
        foreach ($orderedFields as $index => $fieldConfiguration)  {
            $interpretedRow[$fieldConfiguration->getField()] = $this->interpretField($fieldConfiguration, $row[$index]);
        }

        return $interpretedRow;
    }

    private function injectInterpretedDataIntoEntity($entity, $interpretedData)
    {
        $reflection = new \ReflectionClass($entity);
        foreach ($interpretedData as $field => $value) {
            $setterName = 'set' . ucfirst($field);
            if ($reflection->hasMethod($setterName)) {
                $entity->{$setterName}($value);
                continue;
            }

            if ($reflection->hasProperty($field)) {
                $reflectionAttribute = new \ReflectionProperty($entity, $field);
                if (!($reflectionAttribute->isPublic())) {
                    $reflectionAttribute->setAccessible(true);
                }
                $reflectionAttribute->setValue($value);
                continue;
            }
            throw new \Exception("Unknown or inaccessible field $field");
        }
    }

    /**
     * @return mixed
     */
    public function getAffectedEntities()
    {
        return $this->affectedEntities;
    }

    private function interpretField($fieldConfiguration, $value)
    {
        $interpreter = $this->getInterpreter($fieldConfiguration);
        return $interpreter->interpret($fieldConfiguration, $value);
    }

    private function getInterpreter($fieldConfiguration)
    {
        if ($fieldConfiguration instanceof LookupFieldConfiguration) {
            return $this->literalFieldInterpreter;
        }
        if ($fieldConfiguration instanceof DateTimeFieldConfiguration) {
            return $this->datetimeFieldInterpreter;
        }
        return $this->literalFieldInterpreter;
    }
}