<?php
/**
 * DataMapperInterface.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\interfaces;
 */
namespace webcraftdg\dataPipeline\interfaces;

use webcraftdg\dataPipeline\configs\PipelineConfig;

interface DataMapperInterface
{
    
    /**
     * map
     *
     * @param  array        $rawRecord
     * @param  PipelineConfig $config
     * @param  int|string   $rowNumber
     *
     * @return array
     */
    public function map(array $rawRecord, PipelineConfig $config): array;
}
