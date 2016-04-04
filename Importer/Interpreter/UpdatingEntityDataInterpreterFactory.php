<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter;

use Doctrine\ORM\EntityManager;
use Netdudes\ImporterBundle\Importer\Configuration\EntityConfigurationInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdatingEntityDataInterpreterFactory
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * @var \Symfony\Component\Validator\Validator
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
    public function __construct(EntityManager $entityManager, ValidatorInterface $validator, EventDispatcherInterface $eventDispatcher)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param EntityConfigurationInterface $configuration
     *
     * @return UpdatingEntityDataInterpreter
     */
    public function create(EntityConfigurationInterface $configuration)
    {
        return new UpdatingEntityDataInterpreter($configuration, $this->entityManager, $this->validator, $this->eventDispatcher);
    }
}
