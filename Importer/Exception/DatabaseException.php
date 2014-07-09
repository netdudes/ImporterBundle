<?php

namespace Netdudes\ImporterBundle\Importer\Exception;

class DatabaseException extends \Exception
{
    protected $dataFile;

    /**
     * @param mixed $dataFile
     */
    public function setDataFile($dataFile)
    {
        $this->dataFile = $dataFile;
    }

    /**
     * @return mixed
     */
    public function getDataFile()
    {
        return $this->dataFile;
    }
}
