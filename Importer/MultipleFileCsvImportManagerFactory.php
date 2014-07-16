<?php

namespace Netdudes\ImporterBundle\Importer;

class MultipleFileCsvImportManagerFactory
{
    /**
     * @var CsvImporterFactory
     */
    private $csvImporterFactory;

    function __construct(CsvImporterFactory $csvImporterFactory)
    {
        $this->csvImporterFactory = $csvImporterFactory;
    }

    public function create()
    {
        return new MultipleFileCsvImportManager($this->csvImporterFactory);
    }

} 