<?php
namespace Netdudes\ImporterBundle\Importer\Interpreter;

use Doctrine\ORM\EntityManager;
use Netdudes\ImporterBundle\Importer\Configuration\EntityConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\Field\BooleanFieldConfiguration;
use Netdudes\ImporterBundle\Importer\Configuration\Field\DateTimeFieldConfiguration;
use Netdudes\ImporterBundle\Importer\Configuration\Field\FieldConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\Field\FileFieldConfiguration;
use Netdudes\ImporterBundle\Importer\Configuration\Field\LookupFieldConfiguration;
use Netdudes\ImporterBundle\Importer\Event\Error\InterpreterExceptionEventFactory;
use Netdudes\ImporterBundle\Importer\Event\ImportEvents;
use Netdudes\ImporterBundle\Importer\Event\PostBindDataImportEvent;
use Netdudes\ImporterBundle\Importer\Event\PostFieldInterpretImportEvent;
use Netdudes\ImporterBundle\Importer\Event\PostRowInterpretImportEvent;
use Netdudes\ImporterBundle\Importer\Event\PreBindDataImportEvent;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\InterpreterException;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\InvalidRowException;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\RowSizeMismatchException;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\UnknownOrInaccessibleFieldException;
use Netdudes\ImporterBundle\Importer\Interpreter\Field\BooleanFieldInterpreter;
use Netdudes\ImporterBundle\Importer\Interpreter\Field\DatetimeFieldInterpreter;
use Netdudes\ImporterBundle\Importer\Interpreter\Field\FieldError;
use Netdudes\ImporterBundle\Importer\Interpreter\Field\FieldInterpreterInterface;
use Netdudes\ImporterBundle\Importer\Interpreter\Field\FileFieldInterpreter;
use Netdudes\ImporterBundle\Importer\Interpreter\Field\LiteralFieldInterpreter;
use Netdudes\ImporterBundle\Importer\Interpreter\Field\LookupFieldInterpreter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EntityDataInterpreter implements InterpreterInterface
{
    /**
     * @var EntityConfigurationInterface
     */
    protected $configuration;

    /**
     * @var FileFieldInterpreter
     */
    protected $fileFieldInterpreter;

    /**
     * @var LiteralFieldInterpreter
     */
    protected $literalFieldInterpreter;

    /**
     * @var DatetimeFieldInterpreter
     */
    protected $datetimeFieldInterpreter;

    /**
     * @var LookupFieldInterpreter
     */
    protected $lookupFieldInterpreter;

    /**
     * @var BooleanFieldInterpreter
     */
    protected $booleanFieldInterpreter;

    /**
     * @var array
     */
    protected $internalLookupCache = [];

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var InterpreterExceptionEventFactory
     */
    private $eventFactory;

    /**
     * @param EntityConfigurationInterface     $configuration
     * @param EntityManager                    $entityManager
     * @param ValidatorInterface               $validator
     * @param EventDispatcherInterface         $eventDispatcher
     * @param InterpreterExceptionEventFactory $factory
     */
    public function __construct(
        EntityConfigurationInterface $configuration,
        EntityManager $entityManager,
        ValidatorInterface $validator,
        EventDispatcherInterface $eventDispatcher,
        InterpreterExceptionEventFactory $factory
    ) {
        $this->configuration = $configuration;
        $this->literalFieldInterpreter = new LiteralFieldInterpreter();
        $this->lookupFieldInterpreter = new LookupFieldInterpreter($entityManager, $this->internalLookupCache);
        $this->datetimeFieldInterpreter = new DatetimeFieldInterpreter();
        $this->fileFieldInterpreter = new FileFieldInterpreter();
        $this->booleanFieldInterpreter = new BooleanFieldInterpreter();
        $this->validator = $validator;
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->eventFactory = $factory;
    }

    /**
     * @param array $data
     * @param bool  $associative
     *
     * @return array
     */
    public function interpret(array $data, $associative = true)
    {
        $rowCount = count($data);

        $entities = [];
        foreach ($data as $index => $row) {
            try {
                $entity = $this->interpretRow($row, $associative);

                $event = new PostRowInterpretImportEvent($index, $rowCount);
                $this->eventDispatcher->dispatch(ImportEvents::POST_ROW_INTERPRET, $event);

                $entities[$index] = $entity;
                $this->internalLookupCache[] = $entity;
            } catch (InterpreterException $exception) {
                $exceptionEvent = $this->eventFactory->create($exception, $index);
                $this->eventDispatcher->dispatch(ImportEvents::INTERPRETER_EXCEPTION, $exceptionEvent);

                if ($exceptionEvent->hasFlagToAbort()) {
                    break;
                }
            }
        }

        return $entities;
    }

    /**
     * @param array $row
     * @param bool  $associative
     *
     * @throws InvalidRowException
     * @throws \Exception
     * @throws RowSizeMismatchException
     * @throws UnknownOrInaccessibleFieldException
     *
     * @return object
     */
    protected function interpretRow(array $row, $associative)
    {
        $interpretedData = $associative ? $this->interpretAssociativeRow($row) : $this->interpretOrderedRow($row);
        $entity = $this->getEntity($interpretedData);

        $this->eventDispatcher->dispatch(ImportEvents::PRE_BIND_DATA, new PreBindDataImportEvent($entity, $this));
        $this->injectInterpretedDataIntoEntity($entity, $interpretedData);
        $this->eventDispatcher->dispatch(ImportEvents::POST_BIND_DATA, new PostBindDataImportEvent($entity, $this));

        $validationViolations = $this->validator->validate($entity);
        if ($validationViolations->count() > 0) {
            $errors = [];
            foreach ($validationViolations as $violation) {
                $exception = new InterpreterException($violation->getMessage());
                $error = new FieldError($exception);

                if ($violation->getPropertyPath() !== '') {
                    $error->setFieldName($this->extractFieldNameFromPropertyPath($violation->getPropertyPath()));
                }

                $errors[] = $error;
            }
            throw new InvalidRowException($errors);
        }

        return $entity;
    }

    /**
     * @param array $columns
     *
     * @throws InterpreterException
     * @throws \Exception
     *
     * @return array
     */
    protected function interpretAssociativeRow(array $columns)
    {
        $interpretedRow = [];
        $errors = [];
        foreach ($columns as $fieldName => $value) {
            $fieldConfiguration = $this->configuration->getField($fieldName);

            $fields = $fieldConfiguration->getField();
            $fieldsToInterpret = is_array($fields) ? $fields : [$fields];

            foreach ($fieldsToInterpret as $field) {
                try {
                    $interpretedRow[$field] = $this->interpretField($fieldConfiguration, $value);
                } catch (InterpreterException $exception) {
                    $error = new FieldError($exception);
                    $error->setFieldName($fieldName);
                    $errors[] = $error;
                }
            }
        }

        if (count($errors) > 0) {
            throw new InvalidRowException($errors);
        }

        return $interpretedRow;
    }

    /**
     * @param FieldConfigurationInterface $fieldConfiguration
     *
     * @return FieldInterpreterInterface
     */
    protected function getInterpreter(FieldConfigurationInterface $fieldConfiguration)
    {
        switch ($fieldConfiguration) {
            case ($fieldConfiguration instanceof LookupFieldConfiguration):
                return $this->lookupFieldInterpreter;
            case ($fieldConfiguration instanceof DateTimeFieldConfiguration):
                return $this->datetimeFieldInterpreter;
            case ($fieldConfiguration instanceof FileFieldConfiguration):
                return $this->fileFieldInterpreter;
            case ($fieldConfiguration instanceof BooleanFieldConfiguration):
                return $this->booleanFieldInterpreter;
            default:
                return $this->literalFieldInterpreter;
        }
    }

    /**
     * @param array $row
     *
     * @throws InterpreterException
     * @throws RowSizeMismatchException
     *
     * @return array
     */
    protected function interpretOrderedRow(array $row)
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

    /**
     * @param array $interpretedData
     *
     * @return object
     */
    protected function getEntity(array $interpretedData)
    {
        $class = $this->configuration->getClass();
        $entity = new $class();

        return $entity;
    }

    /**
     * @param FieldConfigurationInterface $fieldConfiguration
     * @param mixed                       $value
     *
     * @throws InterpreterException
     *
     * @return mixed
     */
    private function interpretField(FieldConfigurationInterface $fieldConfiguration, $value)
    {
        $interpreter = $this->getInterpreter($fieldConfiguration);

        $interpretedValue = $interpreter->interpret($fieldConfiguration, $value);

        $event = new PostFieldInterpretImportEvent($fieldConfiguration, $interpretedValue);
        try {
            $this->eventDispatcher->dispatch(ImportEvents::POST_FIELD_INTERPRET, $event);
        } catch (\Exception $e) {
            throw new InterpreterException("The '$value' value could not be interpreted", $e->getCode(), $e);
        }

        $interpretedValue = $event->interpretedValue;

        return $interpretedValue;
    }

    /**
     * @param object $entity
     * @param array  $interpretedData
     *
     * @throws UnknownOrInaccessibleFieldException
     * @throws \Throwable
     * @throws \TypeError
     */
    private function injectInterpretedDataIntoEntity($entity, array $interpretedData)
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        foreach ($interpretedData as $field => $value) {
            if (is_null($value)) {
                continue;
            }
            try {
                $accessor->setValue($entity, $field, $value);
            } catch (AccessException $exception) {
                $class = get_class($entity);
                throw new UnknownOrInaccessibleFieldException("Could not find or it is not accessible field \"$field\" for class \"{$class}\"", 0, $exception);
            }
        }
    }

    /**
     * @param string $propertyPath
     *
     * @throws \Exception
     *
     * @return string
     */
    private function extractFieldNameFromPropertyPath($propertyPath)
    {
        foreach ($this->configuration->getFields() as $key => $field) {
            if ($field->getField() === $propertyPath) {
                return $key;
            }
        }

        return '';
    }
}
