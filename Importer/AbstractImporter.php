<?php

namespace Netdudes\ImporterBundle\Importer;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Netdudes\ImporterBundle\Importer\Configuration\Collection\ConfigurationCollectionInterface;
use Netdudes\ImporterBundle\Importer\Configuration\ConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\EntityConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\RelationshipConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Exception\DatabaseException;
use Netdudes\ImporterBundle\Importer\Interpreter\EntityDataInterpreter;
use Netdudes\ImporterBundle\Importer\Interpreter\InterpreterInterface;
use Netdudes\ImporterBundle\Importer\Interpreter\RelationshipDataInterpreter;

abstract class AbstractImporter implements ImporterInterface
{
    protected $configurationCollection;

    protected $entityManager;

    public function __construct(ConfigurationCollectionInterface $configurationCollection, EntityManager $entityManager)
    {
        $this->configurationCollection = $configurationCollection;
        $this->entityManager = $entityManager;
    }

    protected function importData($configuration, $parsedData, InterpreterInterface $interpreter, $dataIsAssociativeArray, $flush = true)
    {
        $entitiesToPersist = $interpreter->interpret($parsedData, $dataIsAssociativeArray);
        if (is_null($entitiesToPersist)) {
            return;
        }
        foreach ($entitiesToPersist as $entity) {
            try {
                $this->entityManager->persist($entity);
            } catch (ORMException $exception) {
                $exception = new DatabaseException("Error when persisting for entity {$configuration->getClass()}.", 0, $exception);
                throw $exception;
            }
        }
        if ($flush) {
            $this->flush($configuration);
        }
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

    /**
     * @param $configuration
     *
     * @throws \Exception
     * @return EntityDataInterpreter|RelationshipDataInterpreter
     */
    protected function getInterpreterFromConfiguration($configuration)
    {
        if ($configuration instanceof EntityConfigurationInterface) {
            return new EntityDataInterpreter($configuration, $this->entityManager);
        }

        if ($configuration instanceof RelationshipConfigurationInterface) {
            return new RelationshipDataInterpreter($configuration, $this->entityManager);
        }

        $configurationClass = get_class($configuration);
        throw new \Exception("Unknown configuration type \"{{$configurationClass}}\"");
    }
}
