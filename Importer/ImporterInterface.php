<?php

namespace Netdudes\ImporterBundle\Importer;

interface ImporterInterface
{
    public function import($configurationId, $data);

    public function importFile($configurationId, $filename);
}
