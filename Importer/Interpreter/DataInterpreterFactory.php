<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter;

use Netdudes\ImporterBundle\Importer\Configuration\ConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\EntityConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\RelationshipConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\UpdatingEntityConfiguration;
use Netdudes\ImporterBundle\Importer\Configuration\UpdatingEntityConfigurationInterface;

class DataInterpreterFactory
{
    /**
     * @var EntityDataInterpreterFactory
     */
    private $entityDataInterpreterFactory;

    /**
     * @var RelationshipDataInterpreterFactory
     */
    private $relationshipDataInterpreterFactory;

    /**
     * @var UpdatingEntityDataInterpreterFactory
     */
    private $updatingEntityDataInterpreterFactory;

    function __construct(
        EntityDataInterpreterFactory $entityDataInterpreterFactory,
        UpdatingEntityDataInterpreterFactory $updatingEntityDataInterpreterFactory,
        RelationshipDataInterpreterFactory $relationshipDataInterpreterFactory)
    {
        $this->entityDataInterpreterFactory = $entityDataInterpreterFactory;
        $this->relationshipDataInterpreterFactory = $relationshipDataInterpreterFactory;
        $this->updatingEntityDataInterpreterFactory = $updatingEntityDataInterpreterFactory;
    }

    public function create(ConfigurationInterface $configuration)
    {
        return $this->getInterpreterFromConfiguration($configuration);
    }

    /**
     * @param $configuration
     *
     * @throws \Exception
     * @return EntityDataInterpreter|RelationshipDataInterpreter
     */
    protected function getInterpreterFromConfiguration($configuration)
    {
        if ($configuration instanceof UpdatingEntityConfigurationInterface) {
            return $this->updatingEntityDataInterpreterFactory->create($configuration);
        }

        if ($configuration instanceof EntityConfigurationInterface) {
            return $this->entityDataInterpreterFactory->create($configuration);
        }

        if ($configuration instanceof RelationshipConfigurationInterface) {
            return $this->relationshipDataInterpreterFactory->create($configuration);
        }

        $configurationClass = get_class($configuration);
        throw new \Exception("Unknown configuration type \"{{$configurationClass}}\"");
    }
}