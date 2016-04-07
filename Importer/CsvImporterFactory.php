<?php

namespace Netdudes\ImporterBundle\Importer;

use Doctrine\ORM\EntityManager;
use Netdudes\ImporterBundle\Importer\Configuration\ConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Interpreter\DataInterpreterFactory;
use Netdudes\ImporterBundle\Importer\Parser\CsvParser;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CsvImporterFactory
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

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
        $this->eventDispatcher = new EventDispatcher();
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

        return new CsvImporter($configuration, $interpreter, $this->entityManager, $this->csvParser, clone $this->eventDispatcher, $delimiter);
    }

    /**
     * @param EventSubscriberInterface $eventSubscriber
     */
    public function registerEventSubscriber(EventSubscriberInterface $eventSubscriber)
    {
        $this->eventDispatcher->addSubscriber($eventSubscriber);
    }
}
