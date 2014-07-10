<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter;

use Doctrine\ORM\EntityManager;
use Netdudes\ImporterBundle\Importer\Configuration\EntityConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\Field\DateTimeFieldConfiguration;
use Netdudes\ImporterBundle\Importer\Configuration\Field\FieldConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\Field\FileFieldConfiguration;
use Netdudes\ImporterBundle\Importer\Configuration\Field\LookupFieldConfiguration;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\RowSizeMismatchException;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\UnknownOrInaccessibleFieldException;
use Netdudes\ImporterBundle\Importer\Interpreter\Field\DatetimeFieldInterpreter;
use Netdudes\ImporterBundle\Importer\Interpreter\Field\FileFieldInterpreter;
use Netdudes\ImporterBundle\Importer\Interpreter\Field\LiteralFieldInterpreter;
use Netdudes\ImporterBundle\Importer\Interpreter\Field\LookupFieldInterpreter;

class EntityDataInterpreter implements InterpreterInterface
{
    /**
     * @var \Netdudes\ImporterBundle\Importer\Configuration\EntityConfigurationInterface
     */
    protected $configuration;

    protected $affectedEntities;

    protected $fileFieldInterpreter;

    protected $literalFieldInterpreter;

    protected $datetimeFieldInterpreter;

    protected $lookupFieldInterpreter;

    public function __construct(EntityConfigurationInterface $configuration, EntityManager $entityManager)
    {
        $this->configuration = $configuration;
        $this->literalFieldInterpreter = new LiteralFieldInterpreter();
        $this->lookupFieldInterpreter = new LookupFieldInterpreter($entityManager);
        $this->datetimeFieldInterpreter = new DatetimeFieldInterpreter();
        $this->fileFieldInterpreter = new FileFieldInterpreter();
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

    private function interpretField($fieldConfiguration, $value)
    {
        $interpreter = $this->getInterpreter($fieldConfiguration);

        return $interpreter->interpret($fieldConfiguration, $value);
    }

    private function getInterpreter($fieldConfiguration)
    {
        if ($fieldConfiguration instanceof LookupFieldConfiguration) {
            return $this->lookupFieldInterpreter;
        }
        if ($fieldConfiguration instanceof DateTimeFieldConfiguration) {
            return $this->datetimeFieldInterpreter;
        }
        if ($fieldConfiguration instanceof FileFieldConfiguration) {
            return $this->fileFieldInterpreter;
        }

        return $this->literalFieldInterpreter;
    }

    protected function interpretOrderedRow($row)
    {
        $interpretedRow = [];
        $orderedFields = array_values($this->configuration->getFields());
        if (count($orderedFields) !== count($row)) {
            $exception = new RowSizeMismatchException();
            $exception->setExpectedSize(count($orderedFields));
            $exception->setFoundSize(count($row));
            $exception->setRow(implode(',', $row));
            throw $exception;
        }

        /** @var $fieldConfiguration FieldConfigurationInterface */
        foreach ($orderedFields as $index => $fieldConfiguration) {
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
                $reflectionAttribute->setValue($entity, $value);
                continue;
            }
            $class = get_class($entity);
            throw new UnknownOrInaccessibleFieldException("Could not find or it is not accessible field \"$field\" for class \"{$class}\"");
        }
    }

    /**
     * @return mixed
     */
    public function getAffectedEntities()
    {
        return $this->affectedEntities;
    }
}
