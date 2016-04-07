<?php

namespace Netdudes\ImporterBundle\Importer;

use Netdudes\ImporterBundle\Importer\Configuration\Collection\ConfigurationCollection;
use Netdudes\ImporterBundle\Importer\Configuration\Collection\Exception\UndefinedIndexException;
use Netdudes\ImporterBundle\Importer\Configuration\ConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Exception\DatabaseException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MultipleFileCsvImportManager
{
    /**
     * @var CsvImporterFactory
     */
    private $csvImporterFactory;

    /**
     * @var ConfigurationCollection
     */
    private $configurationCollection;

    /**
     * @param CsvImporterFactory $csvImporterFactory
     */
    public function __construct(CsvImporterFactory $csvImporterFactory)
    {
        $this->csvImporterFactory = $csvImporterFactory;
        $this->configurationCollection = new ConfigurationCollection();
    }

    /**
     * @param string                 $configurationKey
     * @param ConfigurationInterface $configuration
     */
    public function addConfiguration($configurationKey, ConfigurationInterface $configuration)
    {
        $this->configurationCollection->add($configurationKey, $configuration);
    }

    /**
     * @param string $configurationKey
     *
     * @return ConfigurationInterface
     * @throws UndefinedIndexException
     */
    public function getConfiguration($configurationKey)
    {
        return $this->configurationCollection->get($configurationKey);
    }

    public function resetConfigurationCollection()
    {
        $this->configurationCollection = new ConfigurationCollection();
    }

    /**
     * @param string       $configurationKey
     * @param UploadedFile $file
     * @param bool         $hasHeaders
     *
     * @throws DatabaseException
     * @throws UndefinedIndexException
     */
    public function importFile($configurationKey, UploadedFile $file, $hasHeaders = true)
    {
        $configuration = $this->configurationCollection->get($configurationKey);
        $importer = $this->csvImporterFactory->create($configuration);
        $importer->importFile($file, $hasHeaders);
    }
}
