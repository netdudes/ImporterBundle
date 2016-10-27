<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter;

use Doctrine\ORM\EntityManager;
use Netdudes\ImporterBundle\Importer\Configuration\EntityConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Event\Error\InterpreterExceptionEventFactory;
use Netdudes\ImporterBundle\Importer\Log\LogInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EntityDataInterpreterFactory
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param EntityManager            $entityManager
     * @param ValidatorInterface       $validator
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        EntityManager $entityManager,
        ValidatorInterface $validator,
        EventDispatcherInterface $eventDispatcher
    )
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param EntityConfigurationInterface $configuration
     * @param LogInterface                 $log
     *
     * @return EntityDataInterpreter
     */
    public function create(EntityConfigurationInterface $configuration, LogInterface $log)
    {
        $interpreterExceptionEventFactory = new InterpreterExceptionEventFactory($log);

        return new EntityDataInterpreter($configuration, $this->entityManager, $this->validator, $this->eventDispatcher, $interpreterExceptionEventFactory);
    }
}
