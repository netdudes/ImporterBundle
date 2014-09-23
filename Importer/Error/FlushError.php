<?php
namespace Netdudes\ImporterBundle\Importer\Error;

class FlushError implements ImporterErrorInterface
{
    /**
     * @var
     */
    private $message;

    function __construct($message)
    {
        $this->message = $message;
    }

    public function getMessage()
    {
        return $this->message;
    }
}