<?php
/**
 * XlsxWriter.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\io\writers
 */
namespace webcraftdg\dataPipeline\io\writers;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use webcraftdg\dataPipeline\configs\PipelineConfig;
use webcraftdg\dataPipeline\interfaces\DataWriterInterface;
use webcraftdg\dataPipeline\configs\ColumnMapping;
use InvalidArgumentException;
use webcraftdg\dataPipeline\context\OutputContext;

class XlsxWriter implements DataWriterInterface
{
    /**
     * @var array
     */
    private array $sheetCursors = [];
    private array $sheetHeaders = [];
    private OutputContext $outputContext;
    /**
     * @var Spreadsheet
     */
    private Spreadsheet $spreadsheet;
    /**
     * $path
     *
     * @var string
     */
    private string|null $path;

    /** @var array<string, Worksheet> */
    private array $sheetsByTitle = [];

    /**
     * constructor
     *
     * @param  \webcraftdg\dataPipeline\configs\PipelineConfig $config
     * @param  array                                           $options
     */
    public function __construct(private PipelineConfig $config, private array $options = [])
    {
        $this->spreadsheet = new Spreadsheet();
        $this->path = ($this->options['path']) ?? null;
           $this->outputContext = new OutputContext(
            colOffset: ($this->options['colOffset']) ?? 1,
            rowOffset: ($this->options['rowOffset']) ?? 1,
            sectionName: ($this->options['sectionName']) ?? 'onglet 1',
            headers: ($this->options['headers']) ?? []
        );
    }

    /**
     * @param string $sheet
     * @return int
     */
    public function nextRow(string $sheet): int
    {
        return $this->sheetCursors[$sheet] ??= -1;
    }


    /**
     * open
     *
     * @return void
     */
    public function open(): void
    {
        if ($this->path === null) {
            throw new InvalidArgumentException('CsvWriter params "path" not found');
        }
    }

    /**
     * write
     *
     * @param  array              $row
     *
     * @return void
     */
    public function write(array $row): void
    {
        /** @var Worksheet $sheet */
        $sheetName = ($this->outputContext !== null && empty($this->outputContext->sectionName)) ? $this->outputContext->sectionName : 'onglet_1';
        $sheet = $this->getOrCreateSheet($sheetName);
        $this->addHeaders($sheetName, $sheet);
        $rowIndex = $this->nextRow($sheetName);
        $rowIndex = ($rowIndex <= 0 && $this->outputContext !== null)? $this->outputContext->rowOffset : $rowIndex;
        $colIndex = ($this->outputContext !== null && empty($this->outputContext->colOffset))? $this->outputContext->colOffset : 1;
        $this->writeRow($row, $sheet, $sheetName, $colIndex, $rowIndex);
    }

    /**
     * write row
     *
     * @param  array                                         $row
     * @param  \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @param  string                                        $sheetName
     * @param  int                                           $colIndex
     * @param  int                                           $rowIndex
     *
     * @return void
     */
    protected function writeRow(array $row, Worksheet $sheet, string $sheetName, int $colIndex, int $rowIndex) : void
    {
        foreach ($row as $value) {
            $sheet->setCellValue([$colIndex, $rowIndex], $value);
            $colIndex++;
        }
        $this->sheetCursors[$sheetName] = $rowIndex + 1;
    }


    /**
     * Undocumented function
     *
     * @param  string                                               $title
     * @param  \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet        $sheet
     *
     * @return void
     */
    private function addHeaders(string $title, Worksheet $sheet): void
    {
       if (isset($this->sheetHeaders[$title]) === false) {
            $headers = $this->outputContext->headers;
            if (empty($headers) === true) {
                $headers = array_map(function(ColumnMapping $column) {
                    return $column->outputKey;
                }, $this->config->columns);
            }
            $rowIndex = ($this->outputContext !== null && empty($this->outputContext->rowOffset))? $this->outputContext->rowOffset : 1;
            $colIndex = ($this->outputContext !== null && empty($this->outputContext->colOffset))? $this->outputContext->colOffset : 1;
            $this->writeRow($headers, $sheet, $title, $colIndex, $rowIndex);
            $this->sheetHeaders[$title] = $headers;
        }
    }

    /**
     * @param string $title
     * @return Worksheet
     */
    private function getOrCreateSheet(string $title): Worksheet
    {
        if (isset($this->sheetsByTitle[$title])) {
            $sheet =  $this->sheetsByTitle[$title];
        } else {
            // Si une feuille existe déjà avec ce titre via PhpSpreadsheet (par sécurité)
            $sheet = $this->spreadsheet->getSheetByName($title);
            if ($sheet instanceof Worksheet) {
                $this->sheetsByTitle[$title] = $sheet;
            } else {
                $sheetNumber = count($this->spreadsheet->getAllSheets());
                if ($sheetNumber === 1 && empty($this->sheetsByTitle) === true) {
                    $sheet = $this->spreadsheet->getActiveSheet();
                } else {
                    // Créer une nouvelle feuille
                    $sheet = $this->spreadsheet->createSheet($sheetNumber);
                }
                $sheet->setTitle($title);
                $this->sheetsByTitle[$title] = $sheet;
            }
        }
        return $sheet;
    }

    /**
     * @param string $filePath
     * @return void
     */
    public function close(): void
    {
        $writer = new Xlsx($this->spreadsheet);
        $writer->save($this->path);
    }
}
