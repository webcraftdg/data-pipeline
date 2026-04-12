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

use webcraftdg\dataPipeline\contexts\OutputContext;
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
     * @param  array         $row
     * @param  OutputContext $context
     *
     * @return void
     */
    public function write(array $row, ?OutputContext $context = null): void;

    /**
     * close
     *
     * @return void
     */
    public function close(): void;
}