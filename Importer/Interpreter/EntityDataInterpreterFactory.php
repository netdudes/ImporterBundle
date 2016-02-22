<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter;

use Doctrine\ORM\EntityManager;
use Netdudes\ImporterBundle\Importer\Configuration\EntityConfigurationInterface;
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
     * @param EntityManager $entityManager
     * @param ValidatorInterface $validator
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        EntityManager $entityManager,
        ValidatorInterface $validator,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param EntityConfigurationInterface $configuration
     *
     * @return EntityDataInterpreter
     */
    public function create(EntityConfigurationInterface $configuration)
    {
        return new EntityDataInterpreter($configuration, $this->entityManager, $this->validator, $this->eventDispatcher);
    }
}
