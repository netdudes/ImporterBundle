<?php
namespace Netdudes\ImporterBundle\Importer\Parser;

use Netdudes\ImporterBundle\Importer\Parser\Exception\ParserException;

class CsvParser implements ParserInterface
{
    /**
     * @param string $data
     * @param bool   $hasHeaders
     * @param string $delimiter
     *
     * @return array
     */
    public function parse($data, $hasHeaders = true, $delimiter = ',')
    {
        $rows = explode("\n", $data);
        if ($hasHeaders) {
            $headerRow = $this->parseLine(array_shift($rows), $delimiter);
        } else {
            $headerRow = range(0, count($this->parseLine($rows[0], $delimiter)));
        }
        $data = [];
        foreach ($rows as $rowIndex => $row) {
            if (!($row = trim($row))) {
                continue;
            }

            $rowParsed = $this->parseLine($row, $delimiter);
            if ($hasHeaders) {
                $this->compareRowLengths($rowParsed, $headerRow, $rowIndex);
            }

            $dataRow = [];
            foreach ($rowParsed as $cellIndex => $cell) {
                $header = $headerRow[$cellIndex];
                $dataRow[$header] = $cell;
            }
            $data[$rowIndex] = $dataRow;
        }

        return $data;
    }

    /**
     * @param string $row
     * @param string $delimiter
     *
     * @return array
     */
    public function parseLine($row, $delimiter = ',')
    {
        $enclosure = '"';

        return str_getcsv($row, $delimiter, $enclosure);
    }

    /**
     * @param int $rowIndex
     *
     * @throws ParserException
     */
    private function compareRowLengths(array $parsedRow, array $headers, $rowIndex)
    {
        $headerColumnsCount = count($headers);
        $rowColumnsCount = count($parsedRow);

        if ($rowColumnsCount !== $headerColumnsCount) {
            $lineNumber = (int) $rowIndex + 2;

            throw new ParserException($lineNumber, "Unable to parse csv at line '$lineNumber'. Column count ($rowColumnsCount) is not the same as the header count ($headerColumnsCount).");
        }
    }
}
