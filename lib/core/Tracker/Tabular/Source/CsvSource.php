
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tracker\Tabular\Source;

use Tracker\Tabular\Schema;

class CsvSource implements SourceInterface
{
    private Schema $schema;
    private \SplFileObject $file;
    private string $encoding;

    public function __construct(Schema $schema, string $fileName, string $delimiter = ',', string $encoding = null)
    {
        ini_set('auto_detect_line_endings', true);
        $this->schema = $schema->getPlainOutputSchema();
        $this->file = new \SplFileObject($fileName, 'r');
        $this->file->setCsvControl($delimiter);
        if ($encoding === null) {
            // try to detect
            $likelyEncodings = [
                        'UTF-8', 'ASCII',
                        'Windows-1252', 'Windows-1251', 'Windows-1254',
                        'ISO-8859-1', 'ISO-8859-2', 'ISO-8859-3', 'ISO-8859-4', 'ISO-8859-5',
                        'ISO-8859-6', 'ISO-8859-7', 'ISO-8859-8', 'ISO-8859-9', 'ISO-8859-10',
                        'ISO-8859-13', 'ISO-8859-14', 'ISO-8859-15', 'ISO-8859-16',
            ];
            mb_detect_order($likelyEncodings);
            $size = min($this->file->getSize(), 1000000);   // just check the first mb
            $content = $this->file->fread($size);

            $encoding = mb_detect_encoding($content, $likelyEncodings, true);
        }
        $this->encoding = $encoding;
    }

    public function getEntries()
    {
        $this->file->fseek(0);

        $headers = $this->file->fgetcsv();
        foreach ($headers as & $header) {
            $header = $this->decode($header);
        }
        $mapping = $this->schema->validateAgainstHeaders($headers);

        while (! $this->file->eof()) {
            $row = $this->file->fgetcsv();

            if (count($row) == 1 && empty($row[0])) {
                continue;
            }

            $data = [];
            foreach ($this->schema->getColumns() as $i => $column) {
                if (! isset($mapping[$i])) {
                    continue;
                }
                $rowIndex = $mapping[$i];
                $data[spl_object_hash($column)] = $this->decode($row[$rowIndex]);
            }

            yield new CsvSourceEntry($data);
        }
    }

    public function getSchema()
    {
        return $this->schema;
    }

    private function decode(string $str): string
    {
        if ($this->encoding) {
            return mb_convert_encoding($str, 'UTF-8', $this->encoding);
        } else {
            return $str;
        }
    }
}