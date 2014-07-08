<?php

namespace Netdudes\ImporterBundle\Importer\Configuration\Collection;

use Netdudes\ImporterBundle\Importer\Configuration\Collection\Exception\UndefinedIndexException;
use Netdudes\ImporterBundle\Importer\Configuration\ConfigurationInterface;
use Traversable;

class ConfigurationCollection implements ConfigurationCollectionInterface
{

    protected $collection = [];

    public function get($key)
    {
        if (array_key_exists($key, $this->collection)) {
            return $this->collection[$key];
        }

        throw new UndefinedIndexException("No configuration for $key found");
    }

    public function add($key, ConfigurationInterface $configuration)
    {
        $this->collection[$key] = $configuration;
    }

    public function all()
    {
        return $this->collection;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->collection);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->collection);
    }
}