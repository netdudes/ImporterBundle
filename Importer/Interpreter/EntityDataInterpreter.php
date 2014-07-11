<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter;

use Doctrine\ORM\EntityManager;
use Netdudes\ImporterBundle\Importer\Configuration\EntityConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\Field\DateTimeFieldConfiguration;
use Netdudes\ImporterBundle\Importer\Configuration\Field\FieldConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\Field\FileFieldConfiguration;
use Netdudes\ImporterBundle\Importer\Configuration\Field\LookupFieldConfiguration;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\InterpreterException;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\RowSizeMismatchException;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\UnknownOrInaccessibleFieldException;
use Netdudes\ImporterBundle\Importer\Interpreter\Field\DatetimeFieldInterpreter;
use Netdudes\ImporterBundle\Importer\Interpreter\Field\FileFieldInterpreter;
use Netdudes\ImporterBundle\Importer\Interpreter\Field\LiteralFieldInterpreter;
use Netdudes\ImporterBundle\Importer\Interpreter\Field\LookupFieldInterpreter;
use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class EntityDataInterpreter implements InterpreterInterface
{
    /**
     * @var \Netdudes\ImporterBundle\Importer\Configuration\EntityConfigurationInterface
     */
    protected $configuration;

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
        $entities = [];
        foreach ($data as $index => $row) {
            try {
                $entities[$index] = $this->interpretRow($row, $associative);
            } catch (InterpreterException $exception) {
                $this->handleInterpreterError($exception, $index, $row);
            }
        }

        return $entities;
    }

    protected function interpretRow(array $row, $associative)
    {
        $class = $this->configuration->getClass();
        $entity = new $class;
        $interpretedData = $associative ? $this->interpretAssociativeRow($row) : $this->interpretOrderedRow($row);
        $this->injectInterpretedDataIntoEntity($entity, $interpretedData);
        return $entity;

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
        $accessor = PropertyAccess::createPropertyAccessor();
        foreach ($interpretedData as $field => $value) {
            try {
                $accessor->setValue($entity, $field, $value);
            } catch (AccessException $exception) {
                $class = get_class($entity);
                throw new UnknownOrInaccessibleFieldException("Could not find or it is not accessible field \"$field\" for class \"{$class}\"", 0, $exception);
            }
        }
    }

    private function handleInterpreterError($exception, $index, $row)
    {
        throw $exception;
    }
}