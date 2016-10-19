<?php

namespace Netdudes\ImporterBundle\Importer;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Netdudes\ImporterBundle\Importer\Configuration\ConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Error\CsvHeadersError;
use Netdudes\ImporterBundle\Importer\Error\Error;
use Netdudes\ImporterBundle\Importer\Error\Handler\ImporterErrorHandlerInterface;
use Netdudes\ImporterBundle\Importer\Error\ImporterErrorInterface;
use Netdudes\ImporterBundle\Importer\Interpreter\Error\Handler\InterpreterErrorHandlerInterface;
use Netdudes\ImporterBundle\Importer\Interpreter\InterpreterInterface;
use Netdudes\ImporterBundle\Importer\Log\CsvLog;
use Netdudes\ImporterBundle\Importer\Parser\CsvParser;

class CsvImporter implements ImporterInterface
{
    /**
     * @var CsvParser
     */
    protected $parser;

    /**
     * @var string
     */
    protected $delimiter;

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
     * @var CsvLog
     */
    protected $log;

    /**
     * @var bool
     */
    protected $csvHasHeaders = true;

    /**
     * @param ConfigurationInterface $configuration
     * @param InterpreterInterface   $interpreter
     * @param EntityManager          $entityManager
     * @param CsvParser              $parser
     * @param CsvLog                 $log
     * @param string                 $delimiter
     */
    public function __construct(
        ConfigurationInterface $configuration,
        InterpreterInterface $interpreter,
        EntityManager $entityManager,
        CsvParser $parser,
        CsvLog $log,
        $delimiter = ','
    ) {
        $this->parser = $parser;
        $this->delimiter = $delimiter;
        $this->configuration = $configuration;
        $this->entityManager = $entityManager;
        $this->interpreter = $interpreter;
        $this->log = $log;
    }

    /**
     * @return boolean
     */
    public function csvHasHeaders()
    {
        return $this->csvHasHeaders;
    }

    /**
     * @param boolean $csvHasHeaders
     */
    public function setCsvHasHeaders($csvHasHeaders)
    {
        $this->csvHasHeaders = $csvHasHeaders;
    }

    /**
     * @param string $delimiter
     */
    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;
    }

    /**
     * {@inheritdoc}
     */
    public function registerInterpreterErrorHandler(InterpreterErrorHandlerInterface $lineErrorHandler)
    {
        $this->interpreter->registerErrorHandler($lineErrorHandler);
    }

    /**
     * {@inheritdoc}
     */
    public function registerImporterErrorHandler(ImporterErrorHandlerInterface $fileErrorHandler)
    {
        $this->importerErrorHandlers[] = $fileErrorHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * @param string $csv
     * @param bool   $dryRun
     */
    public function import($csv, $dryRun = false)
    {
        try {
            if ($this->csvHasHeaders() && !$this->checkHeadersAreValid($csv)) {
                return;
            }

            $rawData = explode("\n", $csv);
            if ($this->csvHasHeaders()) {
                $rawData = array_slice($rawData, 1);
            }
            $this->log->setRawCsvLines($rawData);

            $parsedData = $this->parser->parse($csv, $this->csvHasHeaders(), $this->delimiter);

            $this->importData($parsedData, $this->csvHasHeaders(), !$dryRun);
        } catch (\Throwable $throwable) {
            $message = sprintf(
                '%s: %s (uncaught exception) at %s line %d while running import.',
                get_class($throwable),
                $throwable->getMessage(),
                $throwable->getFile(),
                $throwable->getLine()
            );
            $this->log->addCriticalError($message);
        }
    }

    /**
     * @param array $parsedData
     * @param bool  $dataIsAssociativeArray
     * @param bool  $flush
     */
    protected function importData(array $parsedData, $dataIsAssociativeArray, $flush = true)
    {
        $entities = $this->interpreter->interpret($parsedData, $dataIsAssociativeArray);

        $this->log->setImportedEntities($entities);

        if (count($entities) < 1) {
            return;
        }

        if (!$flush || $this->log->containErrors()) {
            $this->detachEntities($entities);

            return;
        }

        $this->persistEntities($entities);

        try {
            $this->entityManager->flush();
        } catch (ORMException $exception) {
            $message = 'Error when flushing for entity.';
            $this->handleImporterError(new Error($message, $exception));
        }
    }

    /**
     * @param array $entitiesToPersist
     */
    protected function persistEntities(array $entitiesToPersist)
    {
        try {
            foreach ($entitiesToPersist as $entity) {
                $this->entityManager->persist($entity);
            }
        } catch (\Exception $exception) {
            $message = 'Error when persisting for entity.';
            $this->handleImporterError(new Error($message, $exception));
        }
    }

    /**
     * @param array $entities
     */
    protected function detachEntities(array $entities)
    {
        foreach ($entities as $entity) {
            $isUpdated = (bool) $entity->getId();
            if ($isUpdated) {
                $this->entityManager->detach($entity);
            }
        }
    }

    /**
     * @param string $csv
     *
     * @return bool
     */
    protected function checkHeadersAreValid($csv)
    {
        $fieldNames = $this->configuration->getFieldNames();
        $headers = $this->parser->parseLine(explode("\n", $csv)[0], $this->delimiter);

        if (count($invalidHeaders = array_diff($headers, $fieldNames))) {
            $error = new CsvHeadersError($invalidHeaders);
            $this->log->addConfigurationError($error->getMessage());

            return false;
        }

        return true;
    }

    /**
     * @param ImporterErrorInterface $error
     */
    protected function handleImporterError(ImporterErrorInterface $error)
    {
        if (count($this->importerErrorHandlers) == 0) {
            $this->log->addConfigurationError($error->getMessage());
        }

        foreach ($this->importerErrorHandlers as $errorHandler) {
            $errorHandler->handle($error);
        }
    }
}
