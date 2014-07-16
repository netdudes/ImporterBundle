<?php

namespace Netdudes\ImporterBundle\Importer;

use Doctrine\ORM\EntityManager;
use Netdudes\ImporterBundle\Importer\Configuration\ConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\Reader\YamlConfigurationReader;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\RowSizeMismatchException;
use Netdudes\ImporterBundle\Importer\Parser\CsvParser;
use Symfony\Component\Yaml\Parser;

class LegacyFixturesImporterWrapper
{
    /**
     * @var MultipleFileCsvImportManager
     */
    private $multiFileCsvImportManager;

    /**
     * @var Configuration\Reader\YamlConfigurationReader
     */
    private $yamlConfigurationReader;

    public function __construct(MultipleFileCsvImportManager $multiFileCsvImportManager, YamlConfigurationReader $yamlConfigurationReader)
    {
        $this->multiFileCsvImportManager = $multiFileCsvImportManager;
        $this->yamlConfigurationReader = $yamlConfigurationReader;
    }

    /**
     * @param        $files
     * @param array  $arrayConfiguration
     * @param string $currentWorkingDirectory
     *
     * @throws \Exception
     * @throws Interpreter\Exception\RowSizeMismatchException
     */
    public function import($files, array $arrayConfiguration, $currentWorkingDirectory = '')
    {
        $this->multiFileCsvImportManager->resetConfigurationCollection();
        foreach ($arrayConfiguration as $index => $configuration) {
            $configuration = $this->yamlConfigurationReader->readParsedYamlArray($configuration);
            $this->multiFileCsvImportManager->addConfiguration($index, $configuration);
            $file = $this->fixWorkingDirectory($files[$index], $currentWorkingDirectory);
            $this->multiFileCsvImportManager->importFile($index, $file);
        }
    }

    /**
     * This is needed as a BC feature for existing fixture loading.
     *
     * @param $file
     * @param $currentWorkingDirectory
     *
     * @return string
     */
    protected function fixWorkingDirectory($file, $currentWorkingDirectory)
    {
        /** Check if the "master" working directory is set and if yes, set it */
        if (!empty($currentWorkingDirectory)) {
            $this->setCwd($currentWorkingDirectory);
        } else {
            /** Set the "master" working directory from the filepath */
            $this->setCwd(dirname($file));
            /** Remove the path from the filename */
            $file = str_replace(dirname($file) . DIRECTORY_SEPARATOR, '', $file);
        }

        /** Build up the filepath and filename */
        $filename = $this->getCwd() . DIRECTORY_SEPARATOR . $file;

        return $filename;
    }

    /**
     * @return string
     */
    public function getCwd()
    {
        return $this->cwd;
    }

    /**
     * @param string $cwd
     *
     * @return LegacyFixturesImporterWrapper
     */
    public function setCwd($cwd)
    {
        $this->cwd = $cwd;

        return $this;
    }
}
