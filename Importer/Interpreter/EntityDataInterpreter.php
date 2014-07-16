<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter;

use Doctrine\ORM\EntityManager;
use Netdudes\ImporterBundle\Importer\Configuration\EntityConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\Exception\UnknownFieldException;
use Netdudes\ImporterBundle\Importer\Configuration\Field\DateTimeFieldConfiguration;
use Netdudes\ImporterBundle\Importer\Configuration\Field\FieldConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\Field\FileFieldConfiguration;
use Netdudes\ImporterBundle\Importer\Configuration\Field\LookupFieldConfiguration;
use Netdudes\ImporterBundle\Importer\Interpreter\Error\Handler\InterpreterErrorHandlerInterface;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\InterpreterException;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\RowSizeMismatchException;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\UnknownColumnException;
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

    protected $internalLookupCache = [];

    /**
     * @var InterpreterErrorHandlerInterface[]
     */
    protected $errorHandlers = [];

    public function __construct(EntityConfigurationInterface $configuration, EntityManager $entityManager)
    {
        $this->configuration = $configuration;
        $this->literalFieldInterpreter = new LiteralFieldInterpreter();
        $this->lookupFieldInterpreter = new LookupFieldInterpreter($entityManager, $this->internalLookupCache);
        $this->datetimeFieldInterpreter = new DatetimeFieldInterpreter();
        $this->fileFieldInterpreter = new FileFieldInterpreter();
    }

    public function interpret($data, $associative = true)
    {
        $entities = [];
        foreach ($data as $index => $row) {
            try {
                $entity = $this->interpretRow($row, $associative);
                $entities[$index] = $entity;
                $this->internalLookupCache[] = $entity;
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
            try {
                $fieldConfiguration = $this->configuration->getField($fieldName);
            } catch (UnknownFieldException $exception) {
                $column = $exception->getField();
                $exception = new UnknownColumnException("Unknown column $column", 0, $exception);
                $exception->setColumn($column);
                throw $exception;
            }
            $interpretedRow[$fieldConfiguration->getField()] = $this->interpretField($fieldConfiguration, $value);
        }

        return $interpretedRow;

    }

    private function interpretField($fieldConfiguration, $value)
    {
        $interpreter = $this->getInterpreter($fieldConfiguration);

        return $interpreter->interpret($fieldConfiguration, $value);
    }

    protected function getInterpreter($fieldConfiguration)
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

    protected function handleInterpreterError($exception, $index, $row)
    {
        if (count($this->errorHandlers) == 0) {
            throw $exception;
        }

        foreach ($this->errorHandlers as $errorHandler) {
            $errorHandler->handle($exception, $index, $row);
        }
    }

    public function registerErrorHandler(InterpreterErrorHandlerInterface $errorHandler)
    {
        $this->errorHandlers[] = $errorHandler;
    }
}
