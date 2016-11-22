<?php
namespace Netdudes\ImporterBundle\Importer\Configuration\Field;

class DateFieldConfiguration extends DateTimeFieldConfiguration
{
    public function __construct()
    {
        $this->format = 'Y-m-d';
    }
}
