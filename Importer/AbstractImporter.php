<?php

namespace Netdudes\ImporterBundle\Importer;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Netdudes\ImporterBundle\Importer\Configuration\Collection\ConfigurationCollectionInterface;
use Netdudes\ImporterBundle\Importer\Configuration\ConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\EntityConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\RelationshipConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Error\ImporterErrorInterface;
use Netdudes\ImporterBundle\Importer\Exception\DatabaseException;
use Netdudes\ImporterBundle\Importer\Exception\ImporterException;
use Netdudes\ImporterBundle\Importer\Interpreter\EntityDataInterpreter;
use Netdudes\ImporterBundle\Importer\Error\Handler\ImporterErrorHandlerInterface;
use Netdudes\ImporterBundle\Importer\Interpreter\Error\Handler\InterpreterErrorHandlerInterface;
use Netdudes\ImporterBundle\Importer\Interpreter\InterpreterInterface;
use Netdudes\ImporterBundle\Importer\Interpreter\RelationshipDataInterpreter;

abstract class AbstractImporter implements ImporterInterface
{
    protected $configuration;

    protected $entityManager;

    protected $importerErrorHandlers = [];

    /**
     * @var Interpreter\InterpreterInterface
     */
    private $interpreter;

    public function __construct(ConfigurationInterface $configuration, InterpreterInterface $interpreter, EntityManager $entityManager)
    {
        $this->configuration = $configuration;
        $this->entityManager = $entityManager;
        $this->interpreter = $interpreter;
    }

    public function registerInterpreterErrorHandler(InterpreterErrorHandlerInterface $lineErrorHandler)
    {
        $this->interpreter->registerErrorHandler($lineErrorHandler);
    }

    public function registerImporterErrorHandler(ImporterErrorHandlerInterface $fileErrorHandler)
    {
        $this->importerErrorHandlers[] = $fileErrorHandler;
    }

    public function registerPostProcess(callable $callable)
    {
        $this->interpreter->registerPostProcess($callable);
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }

    protected function handleImporterError(ImporterErrorInterface $error)
    {
        if (count($this->importerErrorHandlers) == 0) {
            throw new ImporterException($error, $error->getMessage());
        }

        foreach ($this->importerErrorHandlers as $errorHandler) {
            $errorHandler->handle($error);
        }
    }

    protected function importData($parsedData, $dataIsAssociativeArray, $flush = true)
    {
        $entitiesToPersist = $this->interpreter->interpret($parsedData, $dataIsAssociativeArray);
        if (is_null($entitiesToPersist)) {
            return;
        }
        foreach ($entitiesToPersist as $entity) {
            try {
                $this->entityManager->persist($entity);
            } catch (ORMException $exception) {
                $exception = new DatabaseException("Error when persisting for entity {$this->configuration->getClass()}.", 0, $exception);
                throw $exception;
            }
        }
        if ($flush) {
            $this->flush();
        }
        return $entitiesToPersist;
    }

    public function flush()
    {
        try {
            $this->entityManager->flush();
        } catch (ORMException $exception) {
            $exception = new DatabaseException("Error when flushing for entity {$this->configuration->getClass()}.", 0, $exception);
            throw $exception;
        }
    }
}
