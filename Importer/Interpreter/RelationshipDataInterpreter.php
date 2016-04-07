<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter;

use Doctrine\ORM\EntityManager;
use Netdudes\ImporterBundle\Importer\Configuration\RelationshipConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Interpreter\Error\Handler\InterpreterErrorHandlerInterface;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\LookupFieldException;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\MissingAssignementMethodException;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\MissingColumnException;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\RowSizeMismatchException;
use Netdudes\ImporterBundle\Importer\Interpreter\Field\LookupFieldInterpreter;

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
     * @param RelationshipConfigurationInterface $configuration
     * @param EntityManager                      $entityManager
     */
    public function __construct(RelationshipConfigurationInterface $configuration, EntityManager $entityManager)
    {
        $this->configuration = $configuration;
        $this->lookupFieldInterpreter = new LookupFieldInterpreter($entityManager);
        $this->entityManager = $entityManager;
    }

    /**
     * @param array $data
     * @param bool  $associative
     *
     * @return null
     */
    public function interpret(array $data, $associative = true)
    {
        foreach ($data as $row) {
            $this->interpretRow($row, $associative);
        }
    }

    /**
     * @param array $row
     * @param bool  $associative
     *
     * @throws MissingColumnException
     * @throws RowSizeMismatchException
     */
    private function interpretRow(array $row, $associative)
    {
        $associative ? $this->interpretAssociativeRow($row) : $this->interpretOrderedRow($row);
    }

    /**
     * @param array $row
     *
     * @throws MissingAssignementMethodException
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
     * @throws LookupFieldException
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
     * @throws MissingAssignementMethodException
     * @throws RowSizeMismatchException
     */
    private function interpretOrderedRow(array $row)
    {
        if (count($row) !== 2) {
            throw new RowSizeMismatchException("Relationship association data must have two rows");
        }

        $this->interpretValues($row[0], $row[1]);
    }

    /**
     * @param InterpreterErrorHandlerInterface $errorHandler
     */
    public function registerErrorHandler(InterpreterErrorHandlerInterface $errorHandler)
    {
        // TODO: Implement error handler functionality in relationship interpreters
    }

    /**
     * @param callable $callable
     */
    public function registerPostProcess(callable $callable)
    {
        // TODO: Implement registerPostProcess() method.
    }
}
