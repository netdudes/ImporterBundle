<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter;

use Doctrine\ORM\EntityManager;
use Netdudes\ImporterBundle\Importer\Configuration\RelationshipConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Event\Error\InterpreterExceptionEventFactory;
use Netdudes\ImporterBundle\Importer\Log\LogInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RelationshipDataInterpreterFactory
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param EntityManager            $entityManager
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EntityManager $entityManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param RelationshipConfigurationInterface $configuration
     * @param LogInterface                       $log
     *
     * @return RelationshipDataInterpreter
     */
    public function create(RelationshipConfigurationInterface $configuration, LogInterface $log)
    {
        $interpreterExceptionEventFactory = new InterpreterExceptionEventFactory($log);

        return new RelationshipDataInterpreter($configuration, $this->entityManager, $this->eventDispatcher, $interpreterExceptionEventFactory);
    }
}
