<?php

namespace Netdudes\ImporterBundle\Importer;

interface ImporterInterface
{
    public function import($data);

    public function importFile($filename);

    public function getConfiguration();
}
