<?php
/**
 * RecordFormatterInterface.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\interfaces
 */
namespace webcraftdg\dataPipeline\interfaces;

use fractalCms\importExport\models\ImportConfig;

interface RecordFormatterInterface
{
    /**
     * format
     *
     * @param  array        $mappedRow
     * @param  ImportConfig $config
     *
     * @return array
     */
    public function format(array $mappedRow, ImportConfig $config): array;
}