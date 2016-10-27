<?php
namespace Netdudes\ImporterBundle\Importer\Log;

interface LogInterface
{
    /**
     * @param string $errorMessage
     */
    public function addConfigurationError($errorMessage);

    /**
     * @param int    $index
     * @param string $errorMessage
     */
    public function addDataError($index, $errorMessage);

    /**
     * @param string $errorErrorMessage
     */
    public function addCriticalError($errorErrorMessage);

    /**
     * @return \string[]
     */
    public function getDataErrors();

    /**
     * @return string[]
     */
    public function getConfigurationErrors();

    /**
     * @return \string[]
     */
    public function getCriticalErrors();

    /**
     * @return bool
     */
    public function hasErrors();

    /**
     * @param array|null $entities
     */
    public function setImportedEntities(array $entities);
}
