<?php

namespace Netdudes\ImporterBundle\Importer\Configuration;

use Netdudes\ImporterBundle\Importer\Configuration\Field\FieldConfigurationInterface;

interface EntityConfigurationInterface extends ConfigurationInterface
{
    /**
     * @return FieldConfigurationInterface[]
     */
    public function getFields();

    /**
     * @return string
     */
    public function getClass();

    /**
     * @param string $name
     *
     * @return FieldConfigurationInterface
     */
    public function getField($name);
}
