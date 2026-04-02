<?php
/**
 * RowInserterInterface.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\interfaces
 */
namespace webcraftdg\dataPipeline\interfaces;

use fractalCms\importExport\exceptions\InsertResult;
use fractalCms\importExport\models\ImportConfig;

interface RowInserterInterface 
{

    /**
     * insert
     *
     * @param  ImportConfig $config
     * @param  array        $attributes
     * @param  int|string   $rowNumber
     *
     * @return InsertResult
     */
    public function insert(ImportConfig $config, array $attributes, int|string $rowNumber): InsertResult;
}