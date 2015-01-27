<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter;

use Doctrine\ORM\EntityManager;
use Netdudes\ImporterBundle\Importer\Configuration\EntityConfigurationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EntityDataInterpreterFactory
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * @var \Symfony\Component\Validator\Validator
     */
    private $validator;

    public function __construct(EntityManager $entityManager, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    public function create(EntityConfigurationInterface $configuration)
    {
        return new EntityDataInterpreter($configuration, $this->entityManager, $this->validator);
    }
}
