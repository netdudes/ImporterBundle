<?php
namespace Netdudes\ImporterBundle\Importer;

use Netdudes\ImporterBundle\Importer\Configuration\ConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Log\LogInterface;

interface ImporterInterface
{
    /**
     * @param string $data
     * @param bool   $dryRun
     */
    public function import($data, $dryRun = false);

    /**
     * @return ConfigurationInterface
     */
    public function getConfiguration();

    /**
     * @return LogInterface
     */
    public function getLog();
}
