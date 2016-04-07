<?php

namespace Netdudes\ImporterBundle\Importer\Error;

class CsvHeadersError implements ImporterErrorInterface
{
    /**
     * @var array
     */
    private $invalidHeaders = [];

    /**
     * @param array $invalidHeaders
     */
    public function __construct(array $invalidHeaders)
    {
        $this->invalidHeaders = $invalidHeaders;
    }

    /**
     * @return array
     */
    public function getInvalidHeaders()
    {
        return $this->invalidHeaders;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return "Invalid headers were found: " . implode(", ", $this->invalidHeaders);
    }
}
