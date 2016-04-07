<?php

namespace Netdudes\ImporterBundle\Importer;

use Doctrine\ORM\EntityManager;
use Mockery\Exception;
use Netdudes\ImporterBundle\Importer\Configuration\ConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Error\CsvHeadersError;
use Netdudes\ImporterBundle\Importer\Exception\DatabaseException;
use Netdudes\ImporterBundle\Importer\Exception\ImporterException;
use Netdudes\ImporterBundle\Importer\Interpreter\InterpreterInterface;
use Netdudes\ImporterBundle\Importer\Parser\CsvParser;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CsvImporter extends AbstractImporter
{
    /**
     * @var CsvParser
     */
    protected $parser;

    /**
     * @var string
     */
    private $delimiter;

    /**
     * @param ConfigurationInterface   $configuration
     * @param InterpreterInterface     $interpreter
     * @param EntityManager            $entityManager
     * @param CsvParser                $parser
     * @param EventDispatcherInterface $eventDispatcher
     * @param string                   $delimiter
     */
    public function __construct(
        ConfigurationInterface $configuration,
        InterpreterInterface $interpreter,
        EntityManager $entityManager,
        CsvParser $parser,
        EventDispatcherInterface $eventDispatcher,
        $delimiter = ','
    )
    {
        $this->parser = $parser;
        parent::__construct($configuration, $interpreter, $entityManager, $eventDispatcher);
        $this->delimiter = $delimiter;
    }

    /**
     * @param string $filename
     * @param bool   $hasHeaders
     * @param bool   $flush
     *
     * @return array|null|object[]
     * @throws DatabaseException
     */
    public function importFile($filename, $hasHeaders = true, $flush = true)
    {
        $csv = file_get_contents($filename);
        try {
            return $this->import($csv, $hasHeaders, $flush);
        } catch (DatabaseException $exception) {
            $exception->setDataFile($filename);
            throw $exception;
        }
    }

    /**
     * @param array $csv
     * @param bool  $hasHeaders
     * @param bool  $flush
     *
     * @return array|null
     * @throws DatabaseException
     */
    public function import($csv, $hasHeaders = true, $flush = true)
    {
        if (!$this->checkHeadersAreValid($csv)) {
            return [];
        }

        $parsedData = $this->parser->parse($csv, $hasHeaders, $this->delimiter);

        return $this->importData($parsedData, $hasHeaders, $flush);
    }

    /**
     * @param string $delimiter
     */
    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;
    }

    /**
     * @param string $csv
     *
     * @return bool
     * @throws ImporterException
     */
    protected function checkHeadersAreValid($csv)
    {
        $fieldNames = $this->configuration->getFieldNames();
        $headers = $this->parser->parseLine(explode("\n", $csv)[0], $this->delimiter);

        if (count($invalidHeaders = array_diff($headers, $fieldNames))) {
            $this->handleImporterError(new CsvHeadersError($invalidHeaders));

            return false;
        }

        return true;
    }
}
