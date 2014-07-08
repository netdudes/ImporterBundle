<?php

namespace Netdudes\ImporterBundle\Importer;

use Netdudes\ImporterBundle\Importer\Configuration\Collection\ConfigurationCollection;
use Netdudes\ImporterBundle\Importer\Configuration\Collection\ConfigurationCollectionInterface;
use Netdudes\ImporterBundle\Importer\Configuration\ConfigurationInterface;

interface ImporterInterface
{
//    public function addConfiguration(ConfigurationInterface $configuration);
//    public function addConfigurations(ConfigurationCollectionInterface $configurationCollection);
//    public function setConfigurationCollection(ConfigurationCollection $configurationCollection);

    public function import($configurationKey, $data);
    public function importFile($configurationKey, $filename);
//    public function importFiles(array $configurationKeyToFilenameMap);
}