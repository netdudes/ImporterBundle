<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter;

use Doctrine\ORM\EntityManager;
use Netdudes\ImporterBundle\Importer\Configuration\EntityConfigurationInterface;

class EntityDataInterpreterFactory
{

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function create(EntityConfigurationInterface $configuration)
    {
        return new EntityDataInterpreter($configuration, $this->entityManager);
    }

} 