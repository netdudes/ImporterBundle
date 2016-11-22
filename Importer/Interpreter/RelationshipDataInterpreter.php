<?php
namespace Netdudes\ImporterBundle\Importer\Interpreter;

use Doctrine\ORM\EntityManager;
use Netdudes\ImporterBundle\Importer\Configuration\RelationshipConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Event\Error\InterpreterExceptionEventFactory;
use Netdudes\ImporterBundle\Importer\Event\ImportEvents;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\InterpreterException;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\MissingAssignementMethodException;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\MissingColumnException;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\RowSizeMismatchException;
use Netdudes\ImporterBundle\Importer\Interpreter\Field\LookupFieldInterpreter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RelationshipDataInterpreter implements InterpreterInterface
{
    /**
     * @var RelationshipConfigurationInterface
     */
    protected $configuration;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var LookupFieldInterpreter
     */
    private $lookupFieldInterpreter;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var InterpreterExceptionEventFactory
     */
    private $eventFactory;

    /**
     * @param RelationshipConfigurationInterface $configuration
     * @param EntityManager                      $entityManager
     * @param EventDispatcherInterface           $eventDispatcher
     * @param InterpreterExceptionEventFactory   $eventFactory
     */
    public function __construct(
        RelationshipConfigurationInterface $configuration,
        EntityManager $entityManager,
        EventDispatcherInterface $eventDispatcher,
        InterpreterExceptionEventFactory $eventFactory
    ) {
        $this->configuration = $configuration;
        $this->lookupFieldInterpreter = new LookupFieldInterpreter($entityManager);
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->eventFactory = $eventFactory;
    }

    /**
     * @param array $data
     * @param bool  $associative
     *
     * @return void
     */
    public function interpret(array $data, $associative = true)
    {
        foreach ($data as $index => $row) {
            try {
                $this->interpretRow($row, $associative);
            } catch (InterpreterException $exception) {
                $exceptionEvent = $this->eventFactory->create($exception, $index);
                $this->eventDispatcher->dispatch(ImportEvents::INTERPRETER_EXCEPTION, $exceptionEvent);

                if ($exceptionEvent->hasFlagToAbort()) {
                    break;
                }
            }
        }
    }

    /**
     * @param array $row
     * @param bool  $associative
     */
    private function interpretRow(array $row, $associative)
    {
        $associative ? $this->interpretAssociativeRow($row) : $this->interpretOrderedRow($row);
    }

    /**
     * @param array $row
     *
     * @throws MissingColumnException
     */
    private function interpretAssociativeRow($row)
    {
        $ownerLookupFieldName = $this->configuration->getOwnerLookupFieldName();
        $relatedLookupFieldName = $this->configuration->getRelatedLookupFieldName();

        if (!array_key_exists($ownerLookupFieldName, $row)) {
            throw new MissingColumnException("Missing column \"$ownerLookupFieldName\" correspondent to the owner side of the relationship");
        }

        if (!array_key_exists($relatedLookupFieldName, $row)) {
            throw new MissingColumnException("Missing column \"$relatedLookupFieldName\" correspondent to the related side of the relationship");
        }

        $this->interpretValues($row[$ownerLookupFieldName], $row[$relatedLookupFieldName]);
    }

    /**
     * @param mixed $ownerLookupFieldValue
     * @param mixed $relatedLookupFieldValue
     *
     * @throws MissingAssignementMethodException
     */
    private function interpretValues($ownerLookupFieldValue, $relatedLookupFieldValue)
    {
        $ownerEntity = $this->lookupFieldInterpreter->interpret($this->configuration->getOwnerLookupConfigurationField(), $ownerLookupFieldValue);
        $relatedEntity = $this->lookupFieldInterpreter->interpret($this->configuration->getRelatedLookupConfigurationField(), $relatedLookupFieldValue);

        $ownerEntityReflection = new \ReflectionClass($ownerEntity);
        $assignmentMethod = $this->configuration->getAssignmentMethod();

        if (!($ownerEntityReflection->hasMethod($assignmentMethod))) {
            $class = get_class($ownerEntity);
            throw new MissingAssignementMethodException("Missing assignment method for relationship: Entity of class \"$class\" has no method \"$assignmentMethod\"");
        }

        $ownerEntity->{$assignmentMethod}($relatedEntity);
    }

    /**
     * @param array $row
     *
     * @throws RowSizeMismatchException
     */
    private function interpretOrderedRow(array $row)
    {
        if (count($row) !== 2) {
            throw new RowSizeMismatchException('Relationship association data must have two rows');
        }

        $this->interpretValues($row[0], $row[1]);
    }
}
