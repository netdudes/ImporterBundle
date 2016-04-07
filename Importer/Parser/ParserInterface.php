<?php

namespace Netdudes\ImporterBundle\Importer\Parser;

interface ParserInterface
{
    public function parse(array $data, $hasHeaders = true);
}
