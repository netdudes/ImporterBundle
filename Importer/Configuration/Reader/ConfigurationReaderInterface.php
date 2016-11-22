<?php
namespace Netdudes\ImporterBundle\Importer\Configuration\Reader;

use Netdudes\ImporterBundle\Importer\Configuration\Collection\ConfigurationCollection;

interface ConfigurationReaderInterface
{
    /**
     * @param string $filename
     * 
     * @return ConfigurationCollection|null
     */
    public function readFile($filename);
}
