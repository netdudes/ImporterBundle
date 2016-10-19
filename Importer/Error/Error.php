<?php

namespace Netdudes\ImporterBundle\Importer\Error;

class Error implements ImporterErrorInterface
{
    /**
     * @var string
     */
    private $message;

    /**
     * @var \Exception
     */
    private $causingException;

    /**
     * @param string     $readableMessage
     * @param \Exception $causingException
     */
    public function __construct($readableMessage, \Exception $causingException)
    {
        $this->message = $readableMessage;
        $this->causingException = $causingException;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return \Exception
     */
    public function getCausingException()
    {
        return $this->causingException;
    }
}
