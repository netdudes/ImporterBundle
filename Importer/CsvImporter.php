<?php

namespace Netdudes\ImporterBundle\Importer;

use Doctrine\ORM\EntityManager;
use Mockery\Exception;
use Netdudes\ImporterBundle\Importer\Configuration\Collection\ConfigurationCollection;
use Netdudes\ImporterBundle\Importer\Configuration\Collection\ConfigurationCollectionInterface;
use Netdudes\ImporterBundle\Importer\Configuration\ConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\EntityConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\RelationshipConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Error\CsvHeadersError;
use Netdudes\ImporterBundle\Importer\Exception\DatabaseException;
use Netdudes\ImporterBundle\Importer\Interpreter\EntityDataInterpreter;
use Netdudes\ImporterBundle\Importer\Error\Handler\ImporterErrorHandlerInterface;
use Netdudes\ImporterBundle\Importer\Interpreter\Error\Handler\InterpreterErrorHandlerInterface;
use Netdudes\ImporterBundle\Importer\Interpreter\InterpreterInterface;
use Netdudes\ImporterBundle\Importer\Interpreter\RelationshipDataInterpreter;
use Netdudes\ImporterBundle\Importer\Parser\CsvParser;

class CsvImporter extends AbstractImporter
{
    protected $parser;

    /**
     * @var
     */
    private $delimiter;

    public function __construct(ConfigurationInterface $configuration, InterpreterInterface $interpreter, EntityManager $entityManager, CsvParser $parser, $delimiter = ',')
    {
        $this->parser = $parser;
        parent::__construct($configuration, $interpreter, $entityManager);
        $this->delimiter = $delimiter;
    }

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

    public function import($csv, $hasHeaders = true, $flush = true)
    {
        if (!$this->checkHeadersAreValid($csv)) {
            return [];
        }

        $parsedData = $this->parser->parse($csv, $hasHeaders, $this->delimiter);

        return $this->importData($parsedData, $hasHeaders, $flush);
    }

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
