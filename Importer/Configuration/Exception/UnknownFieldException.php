<?php

namespace Netdudes\ImporterBundle\Importer\Configuration\Exception;


class UnknownFieldException extends \Exception
{
    /**
     * @var string
     */
    private $field;

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param string $field
     */
    public function setField($field)
    {
        $this->field = $field;
    }
}
