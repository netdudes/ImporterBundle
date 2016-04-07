<?php

namespace Netdudes\ImporterBundle\Importer\Configuration\Reader\Exception;

class MissingParameterException extends \Exception
{
    /**
     * @var string
     */
    protected $parameter;

    /**
     * @return string
     */
    public function getParameter()
    {
        return $this->parameter;
    }

    /**
     * @param string $parameter
     */
    public function setParameter($parameter)
    {
        $this->parameter = $parameter;
    }
}
