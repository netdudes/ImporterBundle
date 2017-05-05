<?php
namespace Netdudes\ImporterBundle\Importer\Event;

use Symfony\Component\EventDispatcher\Event;

class PostRowInterpretImportEvent extends Event
{
    /**
     * @var int
     */
    private $index;

    /**
     * @var int
     */
    private $rowCount;

    /**
     * @param int $index
     * @param int $rowCount
     */
    public function __construct($index, $rowCount)
    {
        $this->index = $index;
        $this->rowCount = $rowCount;
    }

    /**
     * @return int
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @param int $index
     */
    public function setIndex($index)
    {
        $this->index = $index;
    }

    /**
     * @return int
     */
    public function getRowCount()
    {
        return $this->rowCount;
    }

    /**
     * @param int $rowCount
     */
    public function setRowCount($rowCount)
    {
        $this->rowCount = $rowCount;
    }
}
