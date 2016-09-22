<?php

namespace Netdudes\ImporterBundle\Importer;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Netdudes\ImporterBundle\Importer\Configuration\ConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Error\Handler\ImporterErrorHandlerInterface;
use Netdudes\ImporterBundle\Importer\Error\ImporterErrorInterface;
use Netdudes\ImporterBundle\Importer\Exception\DatabaseException;
use Netdudes\ImporterBundle\Importer\Exception\ImporterException;
use Netdudes\ImporterBundle\Importer\Interpreter\Error\Handler\InterpreterErrorHandlerInterface;
use Netdudes\ImporterBundle\Importer\Interpreter\InterpreterInterface;

abstract class AbstractImporter implements ImporterInterface
{
    /**
     * @var ConfigurationInterface
     */
    protected $configuration;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var ImporterErrorHandlerInterface[]
     */
    protected $importerErrorHandlers = [];

    /**
     * @var InterpreterInterface
     */
    protected $interpreter;

    /**
     * @param ConfigurationInterface $configuration
     * @param InterpreterInterface   $interpreter
     * @param EntityManager          $entityManager
     */
    public function __construct(
        ConfigurationInterface $configuration,
        InterpreterInterface $interpreter,
        EntityManager $entityManager
    ) {
        $this->configuration = $configuration;
        $this->entityManager = $entityManager;
        $this->interpreter = $interpreter;
    }

    /**
     * @param InterpreterErrorHandlerInterface $lineErrorHandler
     */
    public function registerInterpreterErrorHandler(InterpreterErrorHandlerInterface $lineErrorHandler)
    {
        $this->interpreter->registerErrorHandler($lineErrorHandler);
    }

    /**
     * @param ImporterErrorHandlerInterface $fileErrorHandler
     */
    public function registerImporterErrorHandler(ImporterErrorHandlerInterface $fileErrorHandler)
    {
        $this->importerErrorHandlers[] = $fileErrorHandler;
    }

    /**
     * @return ConfigurationInterface
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @throws DatabaseException
     */
    public function flush()
    {
        try {
            $this->entityManager->flush();
        } catch (ORMException $exception) {
            $message = "ORM Error when flushing for entity {$this->configuration->getClass()}.";
            $exception = new DatabaseException($message, $exception);
            throw $exception;
        } catch (DBALException $exception) {
            $message = "DBAL Error when flushing for entity {$this->configuration->getClass()}.";
            $exception = new DatabaseException($message, $exception);
            throw $exception;
        }
    }

    /**
     * @param ImporterErrorInterface $error
     *
     * @throws ImporterException
     */
    protected function handleImporterError(ImporterErrorInterface $error)
    {
        if (count($this->importerErrorHandlers) == 0) {
            throw new ImporterException($error, $error->getMessage());
        }

        foreach ($this->importerErrorHandlers as $errorHandler) {
            $errorHandler->handle($error);
        }
    }

    /**
     * @param array $parsedData
     * @param bool  $dataIsAssociativeArray
     * @param bool  $flush
     *
     * @return null|object[]
     * @throws DatabaseException
     */
    protected function importData(array $parsedData, $dataIsAssociativeArray, $flush = true)
    {
        $entitiesToPersist = $this->interpreter->interpret($parsedData, $dataIsAssociativeArray);

        if (is_null($entitiesToPersist)) {
            return null;
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
}
