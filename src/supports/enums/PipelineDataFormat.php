<?php
/**
 * PipelineDataFormat.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\supports\enums
 */
namespace webcraftdg\dataPipeline\supports\enums;

enum PipelineDataFormat 
{
    const EXCEL = 'xls';
    const EXCEL_X = 'xlsx';
    const CSV = 'csv';
    const XML = 'xml';
    const JSON = 'json';
    const NDJSON = 'ndjson';
    const ARRAY = 'array';
}