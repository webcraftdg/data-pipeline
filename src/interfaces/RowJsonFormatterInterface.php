<?php
/**
 * RowJsonFormatterInterface.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\interfaces
 */
namespace webcraftdg\dataPipeline\interfaces;

use webcraftdg\dataPipeline\configs\PipelineConfig;

interface RowJsonFormatterInterface
{
    /**
     * format
     *
     * @param  array          $mappedRow
     * @param  PipelineConfig $config
     *
     * @return array
     */
    public function format(array $mappedRow, PipelineConfig $config): array;
}