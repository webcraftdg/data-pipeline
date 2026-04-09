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
use Exception;
use InvalidArgumentException;

class ExcelInput implements InputInterface, InputSpreadsheetInterface
{


    private Spreadsheet $spreadsheet;
    private Worksheet $sheet;
    private $maxColumns = 0;
    private $headers = [];
    private $batchSize = 250;

     /**
     * constructor
     *
     * @param  array          $options
     */
    public function __construct(private array $options = [])
    {
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
        try {
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

        } catch (Exception $e)  {
            throw  $e;
        }
    }

    /**
     * read
     *
     * @return iterable
     */
    public function read(): iterable
    {
         try {
    
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

        } catch (Exception $e)  {
            throw  $e;
        }
    }

    /**
     * get Headers
     *
     * @return array
     */
    public function getHeaders(): array
    {
        try {
            $headers = [];
            if ($this->sheet instanceof Worksheet) {
                for($i = $this->getStartColumn();$i <= $this->maxColumns; $i++) {
                    $headers[] = $this->sheet->getCell([$i, 1])->getValue();
                }
            }
            return $headers;
        } catch (Exception $e)  {
            throw  $e;
        }
    }


    /**
     * getRowValues
     *
     * @param int $rowNumber
     * @return array
     * @throws Exception
     */
    protected function getRowValues($rowNumber) : array
    {
        try {
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
        } catch (Exception $e)  {
            throw  $e;
        }
    }


    /**
     * Prepare
     *
     * @param string $filePath
     * @return Spreadsheet
     * @throws NotSupportedException
     */
    public static function prepareSpreadSheet(string $filePath): Spreadsheet
    {
        try {
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
                        $reader->setDelimiter(';');
                        $reader->setEnclosure('');
                        $reader->setInputEncoding('CP1252');
                        $reader->setSheetIndex(0);
                        $reader->setReadDataOnly(true);
                        $spreadsheet = $reader->load($filePath);
                        break;
                    default:
                        throw new InvalidArgumentException("Extension non supportée : " . $extension);
                }
            }
            return $spreadsheet;
        } catch (Exception $e)  {
            throw  $e;
        }
    }

    /**
     * Get start row
     *
     * @return int
     * @throws Exception
     */
    public function getStartRow(): int
    {
        try {
            return 2;
        } catch (Exception $e)  {
            throw  $e;
        }
    }

    /**
     * get end row
     *
     * @param Worksheet $worksheet
     * @return int
     * @throws Exception
     */
    public function getEndRow(Worksheet $worksheet): int
    {
        try {
            return $worksheet->getHighestRow();
        } catch (Exception $e)  {
            throw  $e;
        }
    }

    /**
     * Get start column
     *
     * @return int
     * @throws Exception
     */
    public function getStartColumn(): int
    {
        try {
            return 1;
        } catch (Exception $e)  {
            throw  $e;
        }
    }

    /**
     * close
     *
     * @return void
     */
    public function close(): void
    {
        try {
            if ($this->spreadsheet instanceof Spreadsheet) {
                $this->spreadsheet->disconnectWorksheets();
                unset($this->spreadsheet);
            }
        } catch (Exception $e)  {
            throw  $e;
        }
    }
}
