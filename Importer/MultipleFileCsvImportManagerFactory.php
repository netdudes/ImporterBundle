<?php

namespace Netdudes\ImporterBundle\Importer;

class MultipleFileCsvImportManagerFactory
{
    /**
     * @var CsvImporterFactory
     */
    private $csvImporterFactory;

    /**
     * @param CsvImporterFactory $csvImporterFactory
     */
    public function __construct(CsvImporterFactory $csvImporterFactory)
    {
        $this->csvImporterFactory = $csvImporterFactory;
    }

    /**
     * @return MultipleFileCsvImportManager
     */
    public function create()
    {
        return new MultipleFileCsvImportManager($this->csvImporterFactory);
    }
}
