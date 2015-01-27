<?php

namespace Netdudes\ImporterBundle\Importer\Configuration\Field;

class DateTimeFieldConfiguration extends AbstractFieldConfiguration
{
    protected $format = 'Y-m-d H:i:s';

    /**
     * @return mixed
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param mixed $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }
}
