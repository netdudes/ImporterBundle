<?php
namespace Netdudes\ImporterBundle\Importer;

use Doctrine\ORM\EntityManager;
use Netdudes\ImporterBundle\Importer\Configuration\ConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Interpreter\DataInterpreterFactory;
use Netdudes\ImporterBundle\Importer\Log\CsvLogFactory;
use Netdudes\ImporterBundle\Importer\Parser\CsvParser;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param EntityManager            $entityManager
     * @param CsvParser                $csvParser
     * @param DataInterpreterFactory   $dataInterpreterFactory
     * @param CsvLogFactory            $logFactory
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        EntityManager $entityManager,
        CsvParser $csvParser,
        DataInterpreterFactory $dataInterpreterFactory,
        CsvLogFactory $logFactory,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->entityManager = $entityManager;
        $this->csvParser = $csvParser;
        $this->dataInterpreterFactory = $dataInterpreterFactory;
        $this->logFactory = $logFactory;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param ConfigurationInterface $configuration
     * @param string                 $delimiter
     *
     * @return CsvImporter
     */
    public function create(ConfigurationInterface $configuration, $delimiter = ',')
    {
        $log = $this->logFactory->create();
        $interpreter = $this->dataInterpreterFactory->create($configuration, $log);

        return new CsvImporter($configuration, $interpreter, $this->entityManager, $this->csvParser, $log, $this->eventDispatcher, $delimiter);
    }
}
