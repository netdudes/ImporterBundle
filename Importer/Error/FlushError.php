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
    private $DBALException;

    function __construct($readableMessage, DBALException $DBALException)
    {
        $this->message = $readableMessage;
        $this->DBALException = $DBALException;
    }

    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return DBALException
     */
    public function getDBALException()
    {
        return $this->DBALException;
    }
}