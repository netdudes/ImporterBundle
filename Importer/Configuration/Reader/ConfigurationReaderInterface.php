<?php

namespace Netdudes\ImporterBundle\Importer\Configuration\Reader;

use Netdudes\ImporterBundle\Importer\Configuration\ConfigurationInterface;

interface ConfigurationReaderInterface
{
    public function readFile($filename);
}
