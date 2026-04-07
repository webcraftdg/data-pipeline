<?php
/**
 * ImportInserter.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\interfaces
 */
namespace webcraftdg\dataPipeline\interfaces;

use webcraftdg\dataPipeline\exceptions\OutputResult;

interface OutputInterface {

    /**
     * open
     *
     * @return void
     */
    public function open(): void;
    /**
     * write
     *
     * @param  array        $row
     *
     * @return OutputResult
     */
    public function write(array $row): OutputResult;

    /**
     * close
     *
     * @return void
     */
    public function close(): void;
}