<?php
/**
 * ProcessorInterface.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\interfaces
 */
namespace webcraftdg\dataPipeline\interfaces;

use webcraftdg\dataPipeline\exceptions\ProcessorResult;

interface ProcessorInterface
{

    /**
     * @return string
     */
    public function getName() : string;

    /**
     * Process
     *
     * @param  array        $row
     * @param  array        $options
     *
     * @return ProcessorResult
     */
    public function process(array $row, array $options = []): ProcessorResult;
}
