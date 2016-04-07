<?php

namespace Netdudes\ImporterBundle\Importer\Exception;

use Exception;

class DatabaseException extends \Exception
{
    /**
     * @var string
     */
    protected $dataFile;

    /**
     * @param string    $message
     * @param Exception $previous
     */
    public function __construct($message, Exception $previous)
    {
        parent::__construct($message, 0, $previous);
    }

    /**
     * @return string
     */
    public function getDataFile()
    {
        return $this->dataFile;
    }

    /**
     * @param string $dataFile
     */
    public function setDataFile($dataFile)
    {
        $this->dataFile = $dataFile;
    }
}
