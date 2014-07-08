<?php

namespace Netdudes\ImporterBundle\Importer;

use Doctrine\ORM\EntityManager;
use Netdudes\ImporterBundle\Importer\Configuration\Collection\ConfigurationCollection;
use Netdudes\ImporterBundle\Importer\Configuration\Collection\ConfigurationCollectionInterface;
use Netdudes\ImporterBundle\Importer\Configuration\ConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\EntityConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\Reader\YamlConfigurationReader;
use Netdudes\ImporterBundle\Importer\Configuration\RelationshipConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Interpreter\EntityDataInterpreter;
use Netdudes\ImporterBundle\Importer\Interpreter\RelationshipInterpreter;
use Netdudes\ImporterBundle\Importer\Parser\CsvParser;

class CsvImporter implements ImporterInterface
{
    protected $configurationCollection;
    protected $parser;
    protected $entityManager;
    protected $entityDataInterpreter;
    protected $relationshipDataInterpreter;

    function __construct(ConfigurationCollectionInterface $configurationCollection, EntityManager $entityManager)
    {
        $this->configurationCollection = $configurationCollection;
        $this->parser = new CsvParser();
        $this->entityManager = $entityManager;
        $this->relationshipDataInterpreter = new RelationshipInterpreter($entityManager);
    }

    public static function createFromYamlConfigurationFiles(array $configurationFiles, EntityManager $entityManager)
    {
        $configurationReader = new YamlConfigurationReader();
        foreach ($configurationFiles as $file) {
            $configurationReader->readFile($file);
        }
        return new static($configurationReader->getConfigurationCollection(), $entityManager);
    }


    public function import($configurationKey, $data, $hasHeaders = true, $flush = true)
    {
        $configuration = $this->configurationCollection->get($configurationKey);
        if ($configuration instanceof EntityConfigurationInterface) {
            $this->importEntityData($configuration, $data, $hasHeaders);
            if ($flush) {
                $this->entityManager->flush();
            }
            return;
        }

        if ($configuration instanceof RelationshipConfigurationInterface) {
            $this->importRelationshipData($configuration, $data, $hasHeaders);
            if ($flush) {
                $this->entityManager->flush();
            }
            return;
        }

        throw new \Exception("Unknown configuration type \"{get_class($configuration)}\"");
    }

    public function importFile($configurationKey, $filename, $hasHeaders = true, $flush = true)
    {
        $data = file_get_contents($filename);
        $this->import($configurationKey, $data, $flush);
    }

    private function importEntityData($configuration, $data, $hasHeaders)
    {
        $parsedData = $this->parser->parse($data, $hasHeaders);
        $entityDataInterpreter = new EntityDataInterpreter($configuration, $this->entityManager);
        $interpretedData = $entityDataInterpreter->interpret($parsedData, $hasHeaders);
        foreach ($interpretedData as $entity) {
            $this->entityManager->persist($entity);
        }
    }
}