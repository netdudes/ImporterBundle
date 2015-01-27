<?php

namespace Netdudes\ImporterBundle\Importer\Error;

class CsvHeadersError implements ImporterErrorInterface
{
    private $invalidHeaders = [];

    public function __construct($invalidHeaders)
    {
        $this->invalidHeaders = $invalidHeaders;
    }

    public function getInvalidHeaders()
    {
        return $this->invalidHeaders;
    }

    public function getMessage()
    {
        return "Invalid headers were found: " . implode(", ", $this->invalidHeaders);
    }
}
