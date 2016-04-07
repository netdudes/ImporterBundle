<?php

namespace Netdudes\ImporterBundle\Importer\Interpreter\Exception;

class RowSizeMismatchException extends InterpreterException
{
    /**
     * @var array
     */
    protected $row = [];

    /**
     * @var int
     */
    protected $rowNumber = -1;

    /**
     * @var int
     */
    protected $expectedSize = -1;

    /**
     * @var int
     */
    protected $foundSize = -1;

    /**
     * @var string
     */
    protected $dataFile = 'UNKNOWN';

    /**
     * @return string
     */
    public function __toString()
    {
        return
            $this->message . PHP_EOL .
            "Expected {$this->expectedSize} field, {$this->foundSize} found in {$this->dataFile}, row {$this->rowNumber}" . PHP_EOL .
            $this->row;
    }

    /**
     * @param mixed $expectedSize
     */
    public function setExpectedSize($expectedSize)
    {
        $this->expectedSize = $expectedSize;
    }

    /**
     * @return mixed
     */
    public function getExpectedSize()
    {
        return $this->expectedSize;
    }

    /**
     * @param mixed $foundSize
     */
    public function setFoundSize($foundSize)
    {
        $this->foundSize = $foundSize;
    }

    /**
     * @return mixed
     */
    public function getFoundSize()
    {
        return $this->foundSize;
    }

    /**
     * @param mixed $row
     */
    public function setRow($row)
    {
        $this->row = $row;
    }

    /**
     * @return mixed
     */
    public function getRow()
    {
        return $this->row;
    }

    /**
     * @param mixed $rowNumber
     */
    public function setRowNumber($rowNumber)
    {
        $this->rowNumber = $rowNumber;
    }

    /**
     * @return mixed
     */
    public function getRowNumber()
    {
        return $this->rowNumber;
    }

    /**
     * @return mixed
     */
    public function getDataFile()
    {
        return $this->dataFile;
    }

    /**
     * @param mixed $file
     */
    public function setDataFile($file)
    {
        $this->dataFile = $file;
    }
}
