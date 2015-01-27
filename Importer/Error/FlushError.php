<?php
namespace Netdudes\ImporterBundle\Importer\Error;

use Doctrine\DBAL\DBALException;

class FlushError implements ImporterErrorInterface
{
    /**
     * @var
     */
    private $message;
    /**
     * @var DBALException
     */
    private $causingException;

    public function __construct($readableMessage, \Exception $causingException)
    {
        $this->message = $readableMessage;
        $this->causingException = $causingException;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getCausingException()
    {
        return $this->causingException;
    }
}
