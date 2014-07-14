<?php

namespace Netdudes\ImporterBundle\Importer;

use Doctrine\ORM\EntityManager;
use Mockery\Exception;
use Netdudes\ImporterBundle\Importer\Configuration\Collection\ConfigurationCollection;
use Netdudes\ImporterBundle\Importer\Configuration\Collection\ConfigurationCollectionInterface;
use Netdudes\ImporterBundle\Importer\Configuration\EntityConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\RelationshipConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Exception\DatabaseException;
use Netdudes\ImporterBundle\Importer\Interpreter\EntityDataInterpreter;
use Netdudes\ImporterBundle\Importer\Interpreter\RelationshipDataInterpreter;
use Netdudes\ImporterBundle\Importer\Parser\CsvParser;

class CsvImporter extends AbstractImporter
{
    protected $parser;

    public function __construct(ConfigurationCollectionInterface $configurationCollection, EntityManager $entityManager, CsvParser $parser)
    {
        $this->parser = $parser;
        parent::__construct($configurationCollection, $entityManager);
    }

    public function importFile($configurationId, $filename, $hasHeaders = true, $flush = true)
    {
        $data = file_get_contents($filename);
        try {
            $this->import($configurationId, $data, $hasHeaders, $flush);
        } catch (DatabaseException $exception) {
            $exception->setDataFile($filename);
            throw $exception;
        }
    }

    public function import($configurationId, $csv, $hasHeaders = true, $flush = true)
    {
        $configuration = $this->configurationCollection->get($configurationId);
        $parsedData = $this->parser->parse($csv, $hasHeaders);
        $interpreter = $this->getInterpreterFromConfiguration($configuration);

        $this->importData($configuration, $parsedData, $interpreter, $hasHeaders, $flush);
    }
}
