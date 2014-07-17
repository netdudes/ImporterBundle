<?php

namespace Netdudes\ImporterBundle\Importer;

use Netdudes\ImporterBundle\Importer\Configuration\Collection\ConfigurationCollection;
use Netdudes\ImporterBundle\Importer\Configuration\Collection\Exception\UndefinedIndexException;
use Netdudes\ImporterBundle\Importer\Configuration\ConfigurationInterface;

class MultipleFileCsvImportManager
{
    /**
     * @var CsvImporterFactory
     */
    private $csvImporterFactory;

    /**
     * @var Configuration\Collection\ConfigurationCollection
     */
    private $configurationCollection;

    function __construct(CsvImporterFactory $csvImporterFactory)
    {
        $this->csvImporterFactory = $csvImporterFactory;
        $this->configurationCollection = new ConfigurationCollection();
    }

    public function addConfiguration($configurationKey, ConfigurationInterface $configuration)
    {
        $this->configurationCollection->add($configurationKey, $configuration);
    }

    public function getConfiguration($configurationKey)
    {
        return $this->configurationCollection->get($configurationKey);
    }

    public function resetConfigurationCollection()
    {
        $this->configurationCollection = new ConfigurationCollection();
    }

    public function importFile($configurationKey, $file, $hasHeaders = true)
    {
        $configuration = $this->configurationCollection->get($configurationKey);
        $importer = $this->csvImporterFactory->create($configuration);
        $importer->importFile($file, $hasHeaders);
    }
}