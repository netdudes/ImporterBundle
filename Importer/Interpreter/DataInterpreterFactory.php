<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter;

use Netdudes\ImporterBundle\Importer\Configuration\ConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\EntityConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\RelationshipConfigurationInterface;

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

    function __construct(EntityDataInterpreterFactory $entityDataInterpreterFactory, RelationshipDataInterpreterFactory $relationshipDataInterpreterFactory)
    {
        $this->entityDataInterpreterFactory = $entityDataInterpreterFactory;
        $this->relationshipDataInterpreterFactory = $relationshipDataInterpreterFactory;
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