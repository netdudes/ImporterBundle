<?php

namespace Netdudes\ImporterBundle\Importer\Configuration\Reader;

abstract class AbstractConfigurationReader implements ConfigurationReaderInterface
{
    abstract function readFile($file);
}