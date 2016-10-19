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
     * @return bool
     */
    public function containErrors();

    /**
     * @param array|null $entities
     */
    public function setImportedEntities(array $entities);
}
