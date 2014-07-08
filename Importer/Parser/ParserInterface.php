<?php

namespace Netdudes\ImporterBundle\Importer\Parser;

interface ParserInterface
{
    public function parse($data, $hasHeaders = true);
}