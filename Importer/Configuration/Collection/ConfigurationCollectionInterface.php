<?php

namespace Netdudes\ImporterBundle\Importer\Configuration\Collection;

interface ConfigurationCollectionInterface extends \IteratorAggregate, \Countable
{
    public function all();
    public function get($configurationId);
}
