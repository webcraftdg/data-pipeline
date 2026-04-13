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
use webcraftdg\dataPipeline\contexts\OutputContext;
use webcraftdg\dataPipeline\interfaces\DataWriterInterface;
use webcraftdg\dataPipeline\configs\ColumnMapping;
use InvalidArgumentException;

class XlsxWriter implements DataWriterInterface
{
    /**
     * @var array
     */
    private array $sheetCursors = [];
    private array $sheetHeaders = [];
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
     * @param Spreadsheet $spreadsheet
     */
    public function __construct(private PipelineConfig $config, private array $options = [])
    {
        $this->spreadsheet = new Spreadsheet();
        $this->path = ($this->options['path']) ?? null;
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
     * @param  OutputContext|null $context
     *
     * @return void
     */
    public function write(array $row, ?OutputContext $context = null): void
    {
        /** @var Worksheet $sheet */
        $sheetName = ($context !== null && empty($context->sectionName)) ? $context->sectionName : 'onglet_1';
        $sheet = $this->getOrCreateSheet($sheetName);
        $this->addHeaders($sheetName, $sheet, $context);
        $rowIndex = $this->nextRow($sheetName);
        $rowIndex = ($rowIndex <= 0 && $context !== null)? $context->rowOffset : $rowIndex;
        $colIndex = ($context !== null && empty($context->colOffset))? $context->colOffset : 1;
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
     * @param  \webcraftdg\dataPipeline\contexts\OutputContext|null $context
     *
     * @return void
     */
    private function addHeaders(string $title, Worksheet $sheet, ?OutputContext $context = null): void
    {
       if (isset($this->sheetHeaders[$title]) === false) {
            $headers = ($context !== null && empty($context->headers) === false) ? $context->headers : [];
            if (empty($headers) === true) {
                $headers = array_map(function(ColumnMapping $column) {
                    return $column->outputKey;
                }, $this->config->columns);
            }
            $rowIndex = ($context !== null && empty($context->rowOffset))? $context->rowOffset : 1;
            $colIndex = ($context !== null && empty($context->colOffset))? $context->colOffset : 1;
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
