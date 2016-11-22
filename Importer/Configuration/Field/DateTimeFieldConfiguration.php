<?php
namespace Netdudes\ImporterBundle\Importer\Configuration\Field;

class DateTimeFieldConfiguration extends AbstractFieldConfiguration
{
    /**
     * @var string
     */
    protected $format = 'Y-m-d H:i:s';

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param string $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }
}
