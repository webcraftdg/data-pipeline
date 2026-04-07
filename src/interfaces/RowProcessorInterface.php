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

use webcraftdg\dataPipeline\exceptions\RowProcessorResult;

interface RowProcessorInterface
{

    /**
     * @return string
     */
    public function getName() : string;

    /**
     * Undocumented function
     *
     * @param  array        $row
     * @param  array        $params
     *
     * @return RowProcessorResult
     */
    public function process(array $row, array $params = []): RowProcessorResult;
}