<?php
namespace Netdudes\ImporterBundle\Importer\Error;

use Doctrine\DBAL\DBALException;

class FlushError implements ImporterErrorInterface
{
    /**
     * @var string
     */
    private $message;

    /**
     * @var DBALException
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
     * @return DBALException|\Exception
     */
    public function getCausingException()
    {
        return $this->causingException;
    }
}
