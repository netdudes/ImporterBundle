<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter;

use Netdudes\ImporterBundle\Importer\Configuration\ConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\EntityConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\RelationshipConfigurationInterface;

class InterpreterFactory
{
    public static function create(ConfigurationInterface $configuration)
    {
        if ($configuration instanceof EntityConfigurationInterface) {
            return new EntityDataInterpreter($configuration);
        }

        if ($configuration instanceof RelationshipConfigurationInterface) {
            return new RelationshipInterpreter($configuration);
        }

        throw new \Exception();
    }
}