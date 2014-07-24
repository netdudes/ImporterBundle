<?php

namespace Netdudes\ImporterBundle\Importer;

use Doctrine\ORM\EntityManager;
use Netdudes\ImporterBundle\Importer\Configuration\ConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\EntityConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\RelationshipConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Interpreter\DataInterpreterFactory;
use Netdudes\ImporterBundle\Importer\Interpreter\EntityDataInterpreter;
use Netdudes\ImporterBundle\Importer\Interpreter\EntityDataInterpreterFactory;
use Netdudes\ImporterBundle\Importer\Interpreter\RelationshipDataInterpreter;
use Netdudes\ImporterBundle\Importer\Interpreter\RelationshipDataInterpreterFactory;
use Netdudes\ImporterBundle\Importer\Parser\CsvParser;

class CsvImporterFactory
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * @var Interpreter\EntityDataInterpreter
     */
    private $entityDataInterpreterFactory;

    /**
     * @var Interpreter\RelationshipDataInterpreter
     */
    private $relationshipDataInterpreterFactory;

    /**
     * @var Parser\CsvParser
     */
    private $csvParser;

    /**
     * @var Interpreter\DataInterpreterFactory
     */
    private $dataInterpreterFactory;

    function __construct(EntityManager $entityManager, CsvParser $csvParser, DataInterpreterFactory $dataInterpreterFactory)
    {
        $this->entityManager = $entityManager;
        $this->csvParser = $csvParser;
        $this->dataInterpreterFactory = $dataInterpreterFactory;
    }

    public function create(ConfigurationInterface $configuration, $delimiter)
    {
        $interpreter = $this->dataInterpreterFactory->create($configuration);
        return new CsvImporter($configuration, $interpreter, $this->entityManager, $this->csvParser, $delimiter);
    }
}