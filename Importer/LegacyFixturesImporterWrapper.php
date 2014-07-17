<?php

namespace Netdudes\ImporterBundle\Importer;

use Doctrine\ORM\EntityManager;
use Netdudes\ImporterBundle\Importer\Configuration\ConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\Reader\YamlConfigurationReader;
use Netdudes\ImporterBundle\Importer\Interpreter\Error\Handler\FileLoggerErrorHandler;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\InvalidEntityException;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\RowSizeMismatchException;
use Netdudes\ImporterBundle\Importer\Parser\CsvParser;
use Netdudes\U2\TransferPricingBundle\Importer\Error\InterpreterErrorHandler;
use Netdudes\U2\TransferPricingBundle\Importer\Statistics\TransactionImportStatistics;
use Symfony\Component\Yaml\Parser;

class LegacyFixturesImporterWrapper
{
    /**
     * @var Configuration\Reader\YamlConfigurationReader
     */
    private $yamlConfigurationReader;

    /**
     * @var CsvImporterFactory
     */
    private $csvImporterFactory;

    public function __construct(CsvImporterFactory $csvImporterFactory, YamlConfigurationReader $yamlConfigurationReader)
    {
        $this->yamlConfigurationReader = $yamlConfigurationReader;
        $this->csvImporterFactory = $csvImporterFactory;
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
        $logFile = "demo_data_errors.txt";
        $errorHandler = new FileLoggerErrorHandler(fopen($logFile, "a"));
        foreach ($arrayConfiguration as $index => $configuration) {
            $errorHandler->setCurrentFile($files[$index]);
            $configuration = $this->yamlConfigurationReader->readParsedYamlArray($configuration);
            $importer = $this->csvImporterFactory->create($configuration);
            $file = $this->fixWorkingDirectory($files[$index], $currentWorkingDirectory);
            $csv = file_get_contents($file);
            $errorHandler->setCsv($csv);
            $importer->registerInterpreterErrorHandler($errorHandler);
            try {
                $importer->import($csv, true, true);
            } catch (\Exception $e) {
                echo "\n\nException thrown when importing $file";
                throw $e;
            }
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
