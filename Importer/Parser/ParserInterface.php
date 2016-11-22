<?php
namespace Netdudes\ImporterBundle\Importer\Parser;

interface ParserInterface
{
    /**
     * @param string $data
     * @param bool   $hasHeaders
     *
     * @return array
     */
    public function parse($data, $hasHeaders = true);
}
