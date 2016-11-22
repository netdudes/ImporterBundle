<?php
namespace Netdudes\ImporterBundle\Importer;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use Netdudes\ImporterBundle\Importer\Configuration\ConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Interpreter\InterpreterInterface;
use Netdudes\ImporterBundle\Importer\Log\CsvLog;
use Netdudes\ImporterBundle\Importer\Parser\CsvParser;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param ConfigurationInterface   $configuration
     * @param InterpreterInterface     $interpreter
     * @param EntityManager            $entityManager
     * @param CsvParser                $parser
     * @param CsvLog                   $log
     * @param EventDispatcherInterface $eventDispatcher
     * @param string                   $delimiter
     */
    public function __construct(
        ConfigurationInterface $configuration,
        InterpreterInterface $interpreter,
        EntityManager $entityManager,
        CsvParser $parser,
        CsvLog $log,
        EventDispatcherInterface $eventDispatcher,
        $delimiter = ','
    ) {
        $this->parser = $parser;
        $this->delimiter = $delimiter;
        $this->configuration = $configuration;
        $this->entityManager = $entityManager;
        $this->interpreter = $interpreter;
        $this->log = $log;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return bool
     */
    public function csvHasHeaders()
    {
        return $this->csvHasHeaders;
    }

    /**
     * @param bool $csvHasHeaders
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

        if (!$flush || $this->log->hasErrors()) {
            $this->detachEntities($entities);

            return;
        }

        $this->persistEntities($entities);

        try {
            $this->entityManager->flush();
        } catch (DBALException $exception) {
            $errorMessage = $this->handleDBALException($exception);
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
        } catch (DBALException $exception) {
            $errorMessage = $this->resolveDBALException($exception);
            $this->log->addConfigurationError($errorMessage);
        } catch (\Exception $exception) {
            $errorMessage = 'Error when persisting for entity.';
            $this->log->addConfigurationError($errorMessage);
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
            $errorMessage = 'One or more headers in the imported file are not valid: ' . implode(', ', $invalidHeaders);
            $this->log->addConfigurationError($errorMessage);

            return false;
        }

        return true;
    }

    /**
     * @param DBALException $exception
     *
     * @return string
     */
    private function handleDBALException(DBALException $exception)
    {
        $sqlServerCode = isset($exception->getPrevious()->errorInfo[1]) ? $exception->getPrevious()->errorInfo[1] : -1;
        switch ($sqlServerCode) {
            case 1062:
                $errorMessage = 'Duplicate entry found. It is not possible to import two or more entries with the same values on unique fields.';
                $this->log->addConfigurationError($errorMessage);
            default:
                throw $exception;
        }
    }
}
