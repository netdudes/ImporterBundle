<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter;

use Doctrine\ORM\EntityManager;
use Netdudes\ImporterBundle\Importer\Configuration\RelationshipConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\MissingAssignementMethodException;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\MissingColumnException;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\RowSizeMismatchException;
use Netdudes\ImporterBundle\Importer\Interpreter\Field\LookupFieldInterpreter;

class RelationshipDataInterpreter implements InterpreterInterface
{
    /**
     * @var \Netdudes\ImporterBundle\Importer\Configuration\RelationshipConfigurationInterface
     */
    protected $configuration;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    public function __construct(RelationshipConfigurationInterface $configuration, EntityManager $entityManager)
    {
        $this->configuration = $configuration;
        $this->lookupFieldInterpreter = new LookupFieldInterpreter($entityManager);
        $this->entityManager = $entityManager;
    }

    public function interpret($data, $associative = true)
    {
        foreach ($data as $row) {
            $this->interpretRow($row, $associative);
        }
    }

    private function interpretRow($row, $associative)
    {
        $associative ? $this->interpretAssociativeRow($row) : $this->interpretOrderedRow($row);
    }

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

    private function interpretOrderedRow($row)
    {
        if (count($row) !== 2) {
            throw new RowSizeMismatchException("Relationship association data must have two rows");
        }

        $this->interpretValues($row[0], $row[1]);
    }
}
