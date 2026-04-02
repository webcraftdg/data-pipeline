<?php
/**
 * RowProcessorInterface.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\interfaces
 */
namespace webcraftdg\dataPipeline\interfaces;

use fractalCms\importExport\exceptions\RowProcessorResult;
use fractalCms\importExport\runtime\contexts\Export as ExportContext;

interface RowProcessorInterface
{

    /**
     * @return string
     */
    public function getName() : string;

    /**
     * process
     *
     * @param  array              $row
     * @param  ExportContext      $context
     * @param  array              $params
     *
     * @return RowProcessorResult
     */
    public function process(array $row, ExportContext $context, array $params = []): RowProcessorResult;
}