<?php

namespace Netdudes\ImporterBundle\Importer\Parser\Exception;

class ParserException extends \Exception
{
    /**
     * @var int
     */
    private $lineNumber;

    /**
     * @param int    $lineNumber
     * @param string $message
     */
    public function __construct($lineNumber, $message = null)
    {
        $this->lineNumber = $lineNumber;

        if (null === $message) {
            $message = "Unable to parse csv at line '$lineNumber'.";
        }

        parent::__construct($message);
    }
}
