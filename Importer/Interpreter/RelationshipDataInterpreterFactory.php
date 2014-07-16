<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter;

use Doctrine\ORM\EntityManager;
use Netdudes\ImporterBundle\Importer\Configuration\RelationshipConfigurationInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface;

class RelationshipDataInterpreterFactory
{

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function create(RelationshipConfigurationInterface $configuration)
    {
        return new RelationshipDataInterpreter($configuration, $this->entityManager);
    }

} 