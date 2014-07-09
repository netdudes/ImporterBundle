<?php

namespace Netdudes\ImporterBundle\Importer;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Mockery\Exception;
use Netdudes\ImporterBundle\Importer\Configuration\Collection\ConfigurationCollection;
use Netdudes\ImporterBundle\Importer\Configuration\Collection\ConfigurationCollectionInterface;
use Netdudes\ImporterBundle\Importer\Configuration\ConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\EntityConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\Reader\YamlConfigurationReader;
use Netdudes\ImporterBundle\Importer\Configuration\RelationshipConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Exception\DatabaseException;
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
                $this->flush($configuration);
            }
            return;
        }

        if ($configuration instanceof RelationshipConfigurationInterface) {
            $this->importRelationshipData($configuration, $data, $hasHeaders);
            if ($flush) {
                $this->flush($configuration);
            }
            return;
        }

        throw new \Exception("Unknown configuration type \"{get_class($configuration)}\"");
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

    private function importEntityData($configuration, $data, $hasHeaders)
    {
        $parsedData = $this->parser->parse($data, $hasHeaders);
        $entityDataInterpreter = new EntityDataInterpreter($configuration, $this->entityManager);
        $interpretedData = $entityDataInterpreter->interpret($parsedData, $hasHeaders);
        if (is_null($interpretedData)) {
            return;
        }
        foreach ($interpretedData as $entity) {
            try {
                $this->entityManager->persist($entity);
            } catch (ORMException $exception) {
                $exception = new DatabaseException("Error when persisting for entity {$configuration->getClass()}.", 0, $exception);
                throw $exception;
            }
        }
    }

    private function importRelationshipData($configuration, $data, $hasHeaders)
    {
    }

    protected function flush(ConfigurationInterface $configuration)
    {
        try {
            $this->entityManager->flush();
        } catch (ORMException $exception) {
            $exception = new DatabaseException("Error when flushing for entity {$configuration->getClass()}.", 0, $exception);
            throw $exception;
        }
    }
}