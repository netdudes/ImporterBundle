<?php

namespace Netdudes\ImporterBundle\Importer;

use Doctrine\ORM\EntityManager;
use Netdudes\ImporterBundle\Importer\Configuration\ConfigurationInterface;
use Netdudes\ImporterBundle\Importer\Configuration\Reader\YamlConfigurationReader;
use Netdudes\ImporterBundle\Importer\Interpreter\Exception\RowSizeMismatchException;

class FixturesImporterWrapper
{

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * The current working directory for looking up files
     * @var string
     */
    protected $cwd;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
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
        $configurationReader = new YamlConfigurationReader();
        $configurationReader->readParsedYamlArray($arrayConfiguration);
        $configuration = $configurationReader->getConfigurationCollection();

        $importer = new CsvImporter($configuration, $this->entityManager);

        foreach ($files as $index => $file) {
            $file = $this->fixWorkingDirectory($file, $currentWorkingDirectory);
            $key = array_keys($configuration->all())[$index];
            $data = file_get_contents($file);
            try {
                $importer->import($key, $data, $this->areThereHeadersInTheData($configuration->get($key), $data));
            } catch (RowSizeMismatchException $e) {
                $e->setDataFile($file);
                echo $e;
                throw $e;
            }
        }
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
     * @return FixturesImporterWrapper
     */
    public function setCwd($cwd)
    {
        $this->cwd = $cwd;

        return $this;
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
     * @param $configuration
     * @param $data
     *
     * @return bool
     */
    protected function areThereHeadersInTheData(ConfigurationInterface $configuration, $data)
    {
        $firstRow = str_getcsv(explode("\n", $data)[0]);
        $fieldNames = $configuration->getFieldNames();

        foreach ($firstRow as $header) {
            if (!in_array($header, $fieldNames, true)) {
                return false;
            }
        }

        return true;
    }
}
