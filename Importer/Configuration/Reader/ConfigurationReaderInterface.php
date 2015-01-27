<?php

namespace Netdudes\ImporterBundle\Importer\Configuration\Reader;


interface ConfigurationReaderInterface
{
    public function readFile($filename);
}
