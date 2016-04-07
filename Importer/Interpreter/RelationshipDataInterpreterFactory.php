<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter;

use Doctrine\ORM\EntityManager;
use Netdudes\ImporterBundle\Importer\Configuration\RelationshipConfigurationInterface;

class RelationshipDataInterpreterFactory
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param RelationshipConfigurationInterface $configuration
     * 
     * @return RelationshipDataInterpreter
     */
    public function create(RelationshipConfigurationInterface $configuration)
    {
        return new RelationshipDataInterpreter($configuration, $this->entityManager);
    }
}
