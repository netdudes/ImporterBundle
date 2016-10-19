<?php

namespace Netdudes\ImporterBundle\Importer;

use Doctrine\ORM\EntityManager;
use Netdudes\ImporterBundle\Importer\Configuration\ConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Interpreter\DataInterpreterFactory;
use Netdudes\ImporterBundle\Importer\Log\CsvLogFactory;
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
     * @var CsvLogFactory
     */
    private $logFactory;

    /**
     * @param EntityManager          $entityManager
     * @param CsvParser              $csvParser
     * @param DataInterpreterFactory $dataInterpreterFactory
     * @param CsvLogFactory          $logFactory
     */
    public function __construct(
        EntityManager $entityManager,
        CsvParser $csvParser,
        DataInterpreterFactory $dataInterpreterFactory,
        CsvLogFactory $logFactory
    ) {
        $this->entityManager = $entityManager;
        $this->csvParser = $csvParser;
        $this->dataInterpreterFactory = $dataInterpreterFactory;
        $this->logFactory = $logFactory;
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
        $log = $this->logFactory->create();

        return new CsvImporter($configuration, $interpreter, $this->entityManager, $this->csvParser, $log, $delimiter);
    }
}
