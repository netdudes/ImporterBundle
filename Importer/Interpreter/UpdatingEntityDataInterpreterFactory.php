<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter;

use Doctrine\ORM\EntityManager;
use Netdudes\ImporterBundle\Importer\Configuration\EntityConfigurationInterface;
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

    public function __construct(EntityManager $entityManager, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    public function create(EntityConfigurationInterface $configuration)
    {
        return new UpdatingEntityDataInterpreter($configuration, $this->entityManager, $this->validator);
    }
}
