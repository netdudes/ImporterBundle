<?php

namespace Netdudes\ImporterBundle\Importer;

use Doctrine\ORM\EntityManager;
use Mockery\Exception;
use Netdudes\ImporterBundle\Importer\Configuration\Collection\ConfigurationCollection;
use Netdudes\ImporterBundle\Importer\Configuration\Collection\ConfigurationCollectionInterface;
use Netdudes\ImporterBundle\Importer\Configuration\EntityConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\Reader\YamlConfigurationReader;
use Netdudes\ImporterBundle\Importer\Configuration\RelationshipConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Exception\DatabaseException;
use Netdudes\ImporterBundle\Importer\Parser\CsvParser;

class CsvImporter extends AbstractImporter
{
    protected $parser;

    public function __construct(ConfigurationCollectionInterface $configurationCollection, EntityManager $entityManager, CsvParser $parser)
    {
        $this->parser = $parser;
        parent::__construct($configurationCollection, $entityManager);
    }

    public function importFile($configurationKey, $filename, $hasHeaders = true, $flush = true)
    {
        $data = file_get_contents($filename);
        try {
            $this->import($configurationKey, $data, $flush);
        } catch (DatabaseException $exception) {
            $exception->setDataFile($filename);
            echo $filename;
            throw $exception;
        }
    }

    public function import($configurationKey, $data, $hasHeaders = true, $flush = true)
    {
        $configuration = $this->configurationCollection->get($configurationKey);
        $parsedData = $this->parser->parse($data, $hasHeaders);

        if ($configuration instanceof EntityConfigurationInterface) {
            $this->importEntityData($configuration, $parsedData, $hasHeaders);
            if ($flush) {
                $this->flush($configuration);
            }

            return;
        }

        if ($configuration instanceof RelationshipConfigurationInterface) {
            $this->importRelationshipData($configuration, $parsedData, $hasHeaders);
            if ($flush) {
                $this->flush($configuration);
            }

            return;
        }

        throw new \Exception("Unknown configuration type \"{get_class($configuration)}\"");
    }

}
