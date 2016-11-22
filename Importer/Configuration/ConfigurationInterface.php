<?php
namespace Netdudes\ImporterBundle\Importer\Configuration;

interface ConfigurationInterface
{
    /**
     * @return string[]
     */
    public function getFieldNames();
}
