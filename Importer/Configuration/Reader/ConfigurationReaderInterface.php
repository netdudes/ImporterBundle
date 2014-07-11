<?php

namespace Netdudes\ImporterBundle\Importer\Configuration\Reader;

use Netdudes\ImporterBundle\Importer\Configuration\ConfigurationInterface;

interface ConfigurationReaderInterface
{
    /**
     * @return ConfigurationInterface[]
     */
    public function getConfigurationCollection();

    public function readFile($filename);
}
