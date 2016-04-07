<?php

namespace Netdudes\ImporterBundle\Importer;

use Doctrine\ORM\EntityManager;
use Netdudes\ImporterBundle\Importer\Configuration\ConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Interpreter\DataInterpreterFactory;
use Netdudes\ImporterBundle\Importer\Parser\CsvParser;

class CsvImporterFactory
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var CsvParser
     */
    private $csvParser;

    /**
     * @var DataInterpreterFactory
     */
    private $dataInterpreterFactory;

    /**
     * @param EntityManager          $entityManager
     * @param CsvParser              $csvParser
     * @param DataInterpreterFactory $dataInterpreterFactory
     */
    public function __construct(EntityManager $entityManager, CsvParser $csvParser, DataInterpreterFactory $dataInterpreterFactory)
    {
        $this->entityManager = $entityManager;
        $this->csvParser = $csvParser;
        $this->dataInterpreterFactory = $dataInterpreterFactory;
    }

    /**
     * @param ConfigurationInterface $configuration
     * @param string                 $delimiter
     *
     * @return CsvImporter
     */
    public function create(ConfigurationInterface $configuration, $delimiter = ',')
    {
        $interpreter = $this->dataInterpreterFactory->create($configuration);

        return new CsvImporter($configuration, $interpreter, $this->entityManager, $this->csvParser, $delimiter);
    }
}
