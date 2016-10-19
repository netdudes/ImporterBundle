<?php
namespace Netdudes\ImporterBundle\Importer\Log;

class CsvLogFactory
{
    /**
     * @return CsvLog
     */
    public function create()
    {
        $log = new CsvLog();
        
        return $log;
    }
}
