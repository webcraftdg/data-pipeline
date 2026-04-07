<?php
/**
 * XlsxWriter.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\writers
 */
namespace webcraftdg\dataPipeline\writers;

use fractalCms\importExport\io\interfaces\Writer as WriterInterface;
use fractalCms\importExport\runtime\contexts\Writer as WriterContext;
use fractalCms\importExport\io\exports\writers\WriteTarget;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use InvalidArgumentException;
use Exception;
use Yii;

class XlsxWriter implements WriterInterface
{
    /**
     * @var array
     */
    private array $sheetCursors = [];
    /**
     * @var Spreadsheet
     */
    private Spreadsheet $spreadsheet;
    /**
     * $filePath
     *
     * @var string
     */
    private string $filePath;

    /** @var array<string, Worksheet> */
    private array $sheetsByTitle = [];

    /**
     * @param Spreadsheet $spreadsheet
     */
    public function __construct()
    {
        $this->spreadsheet = new Spreadsheet();
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
     * @param  array $params
     *
     * @return void
     */
    public function open(WriterContext $context): void
    {
        try {
            $path = ($context->absolutePath) ?? null;
            if ($path === null) {
                throw new InvalidArgumentException('CsvWriter params "path" not found');
            }
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * @param WriteTarget $target
     * @param array $row
     * @return void
     * @throws Exception
     */
    public function write(WriteTarget $target, array $row): void
    {
        try {
            /** @var Worksheet $sheet */
            $sheet = $this->getOrCreateSheet($target->sheet);

            $rowIndex = $this->nextRow($target->sheet);
            $rowIndex = ($rowIndex <=0 )? $target->rowNumber : $rowIndex;
            $colIndex = $target->colNumber;

            foreach ($row as $value) {
                $sheet->setCellValue([$colIndex, $rowIndex], $value);
                $colIndex++;
            }

            $this->sheetCursors[$target->sheet] = $rowIndex + 1;
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * @param string $title
     * @return Worksheet
     * @throws Exception
     */
    private function getOrCreateSheet(string $title): Worksheet
    {

        try {
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
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * @param string $filePath
     * @return void
     */
    public function close(WriterContext $writeContext): void
    {
        $writer = new Xlsx($this->spreadsheet);
        $writer->save($writeContext->absolutePath);
    }
}
