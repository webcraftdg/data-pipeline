<?php
/**
 * ExcelReader.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\io\inputs
 */
namespace webcraftdg\dataPipeline\io\inputs;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use webcraftdg\dataPipeline\interfaces\InputInterface;
use webcraftdg\dataPipeline\interfaces\InputSpreadsheetInterface;
use webcraftdg\dataPipeline\interfaces\ValidateRulesInterface;
use InvalidArgumentException;

class ExcelInput implements InputInterface, InputSpreadsheetInterface, ValidateRulesInterface
{


    private Spreadsheet $spreadsheet;
    private Worksheet $sheet;
    private int $maxColumns = 0;
    private array $headers = [];
    private int $batchSize = 250;
    private string $delimiter = ';';
    private string $enclosure = '"';
    private string $inputEncoding = 'ISO-8859-1';

     /**
     * constructor
     *
     * @param  array          $options
     */
    public function __construct(private array $options = [])
    {
        $this->headers = ($options['headers']) ?? [];
        $this->batchSize = ($options['batchSize']) ?? $this->batchSize;
        $this->delimiter = ($options['delimiter']) ?? ';';
        $this->enclosure = ($options['enclosure']) ?? '"';
        $this->inputEncoding = ($options['inputEncoding']) ?? 'ISO-8859-1';
    }

    /**
     * open
     *
     * @param  array  $options
     *
     * @return void
     */
    public function open(): void
    {
         $filePath = ($this->options['path']) ?? '';
        $this->spreadsheet = $this->prepareSpreadSheet($filePath);
        if ($this->spreadsheet instanceof Spreadsheet) {
            $this->sheet = $this->spreadsheet->getActiveSheet();
        }
        if (isset($options['maxColumns']) === true) {
            $this->maxColumns = ($options['maxColumns']);;
        } elseif($this->sheet instanceof Worksheet) {
            $this->maxColumns = Coordinate::columnIndexFromString($this->sheet->getHighestColumn());
        }
        $this->headers = $this->getHeaders();
    }


    /**
     * rules
     *
     * @return array
     */
    public function rules() : array
    {
        return [
            'path' => ['required' => true, 'type' => 'string'],
            'headers' => ['required' => false, 'type' => 'array'],
            'delimiter' => ['required' => false, 'type' => 'string'],
            'enclosure' => ['required' => false, 'type' => 'string'],
            'inputEncoding' => ['required' => false, 'type' => 'array'],
            'maxColumns' => ['required' => false, 'type' => 'integer'],
            'batchSize' => ['required' => false, 'type' => 'integer'],
        ];
    }
    /**
     * read
     *
     * @return iterable
     */
    public function read(): iterable
    {
        $batch = [];
        $indexBatch = 0;
        $startRow = $this->getStartRow();
        $endRow = $this->getEndRow($this->sheet);
        for($i = $startRow;$i <= $endRow; $i ++) {
            $batch[] = $this->getRowValues($i);
            $indexBatch ++;
            if ($indexBatch >= $this->batchSize) {
                yield $batch;
                $batch = [];
                $indexBatch = 0;
            }
        }
        
        if (empty($batch) === false) {
            yield $batch;
        }
    }

    /**
     * get Headers
     *
     * @return array
     */
    public function getHeaders(): array
    {
        $headers = $this->headers;
        if (empty($headers) === true && $this->sheet instanceof Worksheet) {
            for($i = $this->getStartColumn();$i <= $this->maxColumns; $i++) {
                $headers[] = $this->sheet->getCell([$i, 1])->getValue();
            }
        }
        return $headers;
    }


    /**
     * getRowValues
     *
     * @param int $rowNumber
     * @return array
     */
    protected function getRowValues($rowNumber) : array
    {
        $row = [];
        $startCol = $this->getStartColumn();
        $endCol = $this->maxColumns;
        $indexHeader = 0;
        for ($i = $startCol; $i <= $endCol; $i ++) {
            $value = $this->sheet->getCell([$i, $rowNumber])->getValue();
            $row[$this->headers[$indexHeader]]  = $value;
            $indexHeader ++;
        }
        return $row;
    }


    /**
     * Prepare
     *
     * @param string $filePath
     * @return Spreadsheet
     * @throws NotSupportedException
     */
    public function prepareSpreadSheet(string $filePath): Spreadsheet
    {
        $spreadsheet = null;
        if (file_exists($filePath) === true) {
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            switch ($extension) {
                case 'xlsx':
                case 'xls':
                    $spreadsheet = IOFactory::load($filePath);
                    break;
                case 'csv':
                case 'txt':
                    $reader = new Csv();
                    $reader->setDelimiter($this->delimiter);
                    $reader->setEnclosure($this->enclosure);
                    $reader->setInputEncoding($this->inputEncoding);
                    $reader->setSheetIndex(0);
                    $reader->setReadDataOnly(true);
                    $spreadsheet = $reader->load($filePath);
                    break;
                default:
                    throw new InvalidArgumentException("Extension non supportée : " . $extension);
            }
        }
        return $spreadsheet;
    }

    /**
     * Get start row
     *
     * @return int
     */
    public function getStartRow(): int
    {
        return 2;
    }

    /**
     * get end row
     *
     * @param Worksheet $worksheet
     * @return int
     */
    public function getEndRow(Worksheet $worksheet): int
    {
        return $worksheet->getHighestRow();
    }

    /**
     * Get start column
     *
     * @return int
     */
    public function getStartColumn(): int
    {
        return 1;
    }

    /**
     * close
     *
     * @return void
     */
    public function close(): void
    {
        if ($this->spreadsheet instanceof Spreadsheet) {
            $this->spreadsheet->disconnectWorksheets();
            unset($this->spreadsheet);
        }
    }
}
