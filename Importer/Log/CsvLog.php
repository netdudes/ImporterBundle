<?php
namespace Netdudes\ImporterBundle\Importer\Log;

class CsvLog implements LogInterface
{
    /**
     * @var string[]
     */
    protected $configurationErrors = [];

    /**
     * @var string[]
     */
    protected $dataErrors = [];

    /**
     * @var string[]
     */
    protected $criticalErrors = [];

    /**
     * @var array
     */
    protected $rawCsvLines;

    /**
     * @var array
     */
    protected $importedEntities;

    /**
     * {@inheritdoc}
     */
    public function addConfigurationError($errorMessage)
    {
        $this->configurationErrors[] = $errorMessage;
    }

    /**
     * {@inheritdoc}
     */
    public function addDataError($index, $errorMessage)
    {
        $this->dataErrors[$index] = [
            'index_data' => $this->getIndexData($index),
            'error_message' => $errorMessage,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function addCriticalError($errorMessage)
    {
        $this->criticalErrors[] = $errorMessage;
    }

    /**
     * @return \string[]
     */
    public function getDataErrors()
    {
        return $this->dataErrors;
    }

    /**
     * @return string[]
     */
    public function getConfigurationErrors()
    {
        return $this->configurationErrors;
    }

    /**
     * @return \string[]
     */
    public function getCriticalErrors()
    {
        return $this->criticalErrors;
    }

    /**
     * {@inheritdoc}
     */
    public function hasErrors()
    {
        return count($this->getConfigurationErrors()) + count($this->getDataErrors()) + count($this->getCriticalErrors()) > 0;
    }

    /**
     * @param array $rawCsvLines
     */
    public function setRawCsvLines(array $rawCsvLines)
    {
        $this->rawCsvLines = $rawCsvLines;
    }

    /**
     * @return array|null
     */
    public function getImportedEntities()
    {
        return $this->importedEntities;
    }

    /**
     * {@inheritdoc}
     */
    public function setImportedEntities(array $entities)
    {
        $this->importedEntities = $entities;
    }

    /**
     * @param int $index
     *
     * @return string
     */
    private function getIndexData($index)
    {
        if (!is_array($this->rawCsvLines) || !array_key_exists($index, $this->rawCsvLines)) {
            return 'Index data not found';
        }

        return $this->rawCsvLines[$index];
    }
}
