<?php

namespace Netdudes\ImporterBundle\Importer\Error;

interface ImporterErrorInterface
{
    /**
     * @return string
     */
    public function getMessage();
}
