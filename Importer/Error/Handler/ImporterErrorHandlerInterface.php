<?php

namespace Netdudes\ImporterBundle\Importer\Error\Handler;

use Netdudes\ImporterBundle\Importer\Error\ImporterErrorInterface;

interface ImporterErrorHandlerInterface
{
    public function handle(ImporterErrorInterface $error);
}
