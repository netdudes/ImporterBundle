<?php

namespace Netdudes\ImporterBundle\Importer\Configuration\Field;

class DateFieldConfiguration extends DateTimeFieldConfiguration
{
    function __construct()
    {
        $this->format = 'Y-m-d';
    }
}