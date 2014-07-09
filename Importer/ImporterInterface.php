<?php

namespace Netdudes\ImporterBundle\Importer;

interface ImporterInterface
{
    public function import($configurationKey, $data);
    public function importFile($configurationKey, $filename);
}
