<?php
namespace Netdudes\ImporterBundle\Importer\Interpreter;

use Netdudes\ImporterBundle\Importer\Configuration\ConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\EntityConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\RelationshipConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\UpdatingEntityConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Log\LogInterface;

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

    /**
     * @param EntityDataInterpreterFactory         $entityDataInterpreterFactory
     * @param UpdatingEntityDataInterpreterFactory $updatingEntityDataInterpreterFactory
     * @param RelationshipDataInterpreterFactory   $relationshipDataInterpreterFactory
     */
    public function __construct(
        EntityDataInterpreterFactory $entityDataInterpreterFactory,
        UpdatingEntityDataInterpreterFactory $updatingEntityDataInterpreterFactory,
        RelationshipDataInterpreterFactory $relationshipDataInterpreterFactory
    ) {
        $this->entityDataInterpreterFactory = $entityDataInterpreterFactory;
        $this->relationshipDataInterpreterFactory = $relationshipDataInterpreterFactory;
        $this->updatingEntityDataInterpreterFactory = $updatingEntityDataInterpreterFactory;
    }

    /**
     * @param ConfigurationInterface $configuration
     * @param LogInterface           $log
     *
     * @return EntityDataInterpreter|RelationshipDataInterpreter
     */
    public function create(ConfigurationInterface $configuration, LogInterface $log)
    {
        return $this->getInterpreterFromConfiguration($configuration, $log);
    }

    /**
     * @param ConfigurationInterface $configuration
     * @param LogInterface           $log
     *
     * @throws \Exception
     *
     * @return EntityDataInterpreter|RelationshipDataInterpreter|UpdatingEntityDataInterpreter
     */
    protected function getInterpreterFromConfiguration(ConfigurationInterface $configuration, LogInterface $log)
    {
        if ($configuration instanceof UpdatingEntityConfigurationInterface) {
            return $this->updatingEntityDataInterpreterFactory->create($configuration, $log);
        }

        if ($configuration instanceof EntityConfigurationInterface) {
            return $this->entityDataInterpreterFactory->create($configuration, $log);
        }

        if ($configuration instanceof RelationshipConfigurationInterface) {
            return $this->relationshipDataInterpreterFactory->create($configuration, $log);
        }

        $configurationClass = get_class($configuration);
        throw new \Exception("Unknown configuration type \"{{$configurationClass}}\"");
    }
}
