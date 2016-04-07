<?php

namespace Netdudes\ImporterBundle\Importer\Configuration\Collection;

use Netdudes\ImporterBundle\Importer\Configuration\ConfigurationInterface;

interface ConfigurationCollectionInterface extends \IteratorAggregate, \Countable
{
    /**
     * @return ConfigurationInterface[]
     */
    public function all();

    /**
     * @param string $configurationId
     * 
     * @return ConfigurationInterface
     */
    public function get($configurationId);
}
