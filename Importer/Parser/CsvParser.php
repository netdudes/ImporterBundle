<?php

namespace Netdudes\ImporterBundle\Importer\Parser;

class CsvParser implements ParserInterface
{

    public function parse($data, $hasHeaders = true)
    {
        $rows = explode("\n", $data);
        if ($hasHeaders) {
            $headers = $this->parseCsvRow(array_shift($rows));
        } else {
            $headers = range(0, count($this->parseCsvRow($rows[0])));
        }
        $data = [];
        foreach ($rows as $row) {
            if (!($row = trim($row))) {
                continue;
            }
            $row = $this->parseCsvRow($row);
            $dataRow = [];
            foreach ($row as $index => $cell) {
                $dataRow[$headers[$index]] = $cell;
            }
            $data[] = $dataRow;
        }

        return $data;
    }

    private function parseCsvRow($row)
    {
        $delimiter = ',';
        $enclosure = '"';
        return str_getcsv($row, $delimiter, $enclosure);
    }
}