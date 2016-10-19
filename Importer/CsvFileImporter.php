<?php

namespace Netdudes\ImporterBundle\Importer;

class CsvFileImporter extends CsvImporter
{
    /**
     * @param string $filename
     * @param bool   $dryRun
     */
    public function import($filename, $dryRun = false)
    {
        if (!file_exists($filename)) {
            $this->log->addConfigurationError('File not found');

            return;
        }

        $csv = file_get_contents($filename);

        parent::import($csv, $dryRun);
    }
}
