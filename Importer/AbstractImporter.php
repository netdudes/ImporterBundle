<?php

namespace Netdudes\ImporterBundle\Importer;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Netdudes\ImporterBundle\Importer\Configuration\ConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Error\Handler\ImporterErrorHandlerInterface;
use Netdudes\ImporterBundle\Importer\Error\ImporterErrorInterface;
use Netdudes\ImporterBundle\Importer\Event\ImportEvents;
use Netdudes\ImporterBundle\Importer\Event\PostInterpretImportEvent;
use Netdudes\ImporterBundle\Importer\Exception\DatabaseException;
use Netdudes\ImporterBundle\Importer\Exception\ImporterException;
use Netdudes\ImporterBundle\Importer\Interpreter\Error\Handler\InterpreterErrorHandlerInterface;
use Netdudes\ImporterBundle\Importer\Interpreter\InterpreterInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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
     * @var Interpreter\InterpreterInterface
     */
    private $interpreter;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    public function __construct(ConfigurationInterface $configuration, InterpreterInterface $interpreter, EntityManager $entityManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->configuration = $configuration;
        $this->entityManager = $entityManager;
        $this->interpreter = $interpreter;
        $this->eventDispatcher = $eventDispatcher;

        $this->interpreter->registerPostProcess(function ($entity) {
            $this->eventDispatcher->dispatch(ImportEvents::POST_INTERPRET, new PostInterpretImportEvent($entity, $this));
        });
    }

    public function registerInterpreterErrorHandler(InterpreterErrorHandlerInterface $lineErrorHandler)
    {
        $this->interpreter->registerErrorHandler($lineErrorHandler);
    }

    public function registerImporterErrorHandler(ImporterErrorHandlerInterface $fileErrorHandler)
    {
        $this->importerErrorHandlers[] = $fileErrorHandler;
    }

    /**
     * @param callable $callable
     * @deprecated Use events. The interpreter post processes should not be set directly.
     */
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

    /**
     * @param      $parsedData
     * @param      $dataIsAssociativeArray
     * @param bool $flush
     *
     * @return null
     * @throws DatabaseException
     */
    protected function importData($parsedData, $dataIsAssociativeArray, $flush = true)
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
     * @param          $event
     * @param callable $eventListener
     */
    public function addEventListener($event, callable $eventListener)
    {
        $this->eventDispatcher->addListener($event, $eventListener);
    }

    /**
     * @param EventSubscriberInterface $eventSubscriber
     */
    public function addEventSubscriber(EventSubscriberInterface $eventSubscriber)
    {
        $this->eventDispatcher->addSubscriber($eventSubscriber);
    }
}
