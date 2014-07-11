<?php

namespace Netdudes\ImporterBundle\Importer\Configuration\Reader\Exception;

class MissingParameterException extends \Exception
{
    protected $parameter;

    /**
     * @return mixed
     */
    public function getParameter()
    {
        return $this->parameter;
    }

    /**
     * @param mixed $parameter
     */
    public function setParameter($parameter)
    {
        $this->parameter = $parameter;
    }
}
