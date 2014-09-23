<?php

namespace Netdudes\ImporterBundle\Importer\Exception;

use Exception;

class DatabaseException extends \Exception
{
    protected $dataFile;

    public function __construct($message, Exception $previous)
    {
        parent::__construct($message, 0, $previous);
    }

    /**
     * @return mixed
     */
    public function getDataFile()
    {
        return $this->dataFile;
    }

    /**
     * @param mixed $dataFile
     */
    public function setDataFile($dataFile)
    {
        $this->dataFile = $dataFile;
    }
}
