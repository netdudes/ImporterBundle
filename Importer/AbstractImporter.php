<?php

namespace Netdudes\ImporterBundle\Importer;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Netdudes\ImporterBundle\Importer\Configuration\Collection\ConfigurationCollectionInterface;
use Netdudes\ImporterBundle\Importer\Configuration\ConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Exception\DatabaseException;
use Netdudes\ImporterBundle\Importer\Interpreter\EntityDataInterpreter;
use Netdudes\ImporterBundle\Importer\Interpreter\EntityDataInterpreterInterface;
use Netdudes\ImporterBundle\Importer\Interpreter\RelationshipDataInterpreter;
use Netdudes\ImporterBundle\Importer\Interpreter\RelationshipDataInterpreterInterface;

abstract class AbstractImporter implements ImporterInterface
{
    protected $configurationCollection;

    protected $entityManager;

    public function __construct(ConfigurationCollectionInterface $configurationCollection, EntityManager $entityManager)
    {
        $this->configurationCollection = $configurationCollection;
        $this->entityManager = $entityManager;
    }

    protected function importEntityData($configuration, $parsedData, $dataIsAssociativeArray)
    {
        $entityDataInterpreter = new EntityDataInterpreter($configuration, $this->entityManager);
        $interpretedData = $entityDataInterpreter->interpret($parsedData, $dataIsAssociativeArray);
        if (is_null($interpretedData)) {
            return;
        }
        foreach ($interpretedData as $entity) {
            try {
                $this->entityManager->persist($entity);
            } catch (ORMException $exception) {
                $exception = new DatabaseException("Error when persisting for entity {$configuration->getClass()}.", 0, $exception);
                throw $exception;
            }
        }
    }

    protected function importRelationshipData($configuration, $parsedData, $hasHeaders)
    {
        $relationshipDataInterpreter = new RelationshipDataInterpreter($configuration, $this->entityManager);
        $relationshipDataInterpreter->interpret($parsedData, $hasHeaders);
    }

    protected function flush(ConfigurationInterface $configuration)
    {
        try {
            $this->entityManager->flush();
        } catch (ORMException $exception) {
            $exception = new DatabaseException("Error when flushing for entity {$configuration->getClass()}.", 0, $exception);
            throw $exception;
        }
    }
}
