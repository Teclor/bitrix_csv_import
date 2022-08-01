<?php

namespace Custom\Parser;


class DSV
{
    private \SplFileObject $file;
    private array $columns;
    private bool $hasHeader;

    public function __construct(\SplFileObject $file, $hasHeader = true, $delimiter = ';')
    {
        $this->file = $file;
        $this->hasHeader = $hasHeader;
        $this->columns = [];

        $this->file->setFlags(\SplFileObject::READ_CSV);
        $this->file->setCsvControl($delimiter);
    }

    public function getParsed(): array
    {
        $parsedLines = [];

        if ($this->hasHeader) {
            $columns = $this->file->fgetcsv();
            if (empty($this->columns)) {
                $this->setColumns($columns);
            }
        }

        if (count($this->columns) > 0) {
            while (!$this->file->eof()) {
                $row = $this->file->fgetcsv();
                if (!is_null(reset($row))) {
                    $values = [];
                    foreach ($row as $key => $value) {
                        if (isset($this->columns[$key])) {
                            $values[$this->columns[$key]] = $value;
                        }
                    }
                    if (count($values) > 0) {
                        $parsedLines[] = $values;
                    }
                }
            }
        }
        else {
            foreach ($this->file as $row) {
                $parsedLines[] = $row;
            }
        }

        return $parsedLines;
    }

    public function setColumns(array $columns)
    {
        $this->columns = $columns;
    }
}