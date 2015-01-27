<?php

namespace Netdudes\ImporterBundle\Importer\Exception;

use Exception;
use Netdudes\ImporterBundle\Importer\Error\ImporterErrorInterface;

class ImporterException extends \Exception
{
    private $error;

    public function __construct(ImporterErrorInterface $error, $message = "", $code = 0, Exception $previous = null)
    {
        $this->error = $error;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return \Netdudes\ImporterBundle\Importer\Error\ImporterErrorInterface
     */
    public function getError()
    {
        return $this->error;
    }
}
