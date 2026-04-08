<?php
/**
 * InputSpreadsheetInterface.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package  webcraftdg\dataPipeline\interfaces
 */
namespace webcraftdg\dataPipeline\interfaces;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

interface InputSpreadsheetInterface
{

    /**
     * getStarRow
     *
     * @return int
     */
    public function getStartRow(): int;
    /**
     * getStartColumn
     *
     * @return int
     */
    public function getStartColumn(): int;
    
    /**
     * get Headers
     *
     * @return array
     */
    public function getHeaders() : array;

    /**
     * prepareSpreadSheet
     *
     * @param  string      $filePath
     *
     * @return Spreadsheet
     */
    public static function prepareSpreadSheet(string $filePath): Spreadsheet;
}