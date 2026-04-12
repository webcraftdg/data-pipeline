<?php
/**
 * DataWriterInterface.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\interfaces
 */
namespace webcraftdg\dataPipeline\interfaces;

use webcraftdg\dataPipeline\contexts\OutputContext;

interface DataWriterInterface
{
    /**
     * open
     *
     * @return void
     */
    public function open(): void;

    /**
     * write
     *
     * @param  array              $row
     * @param  OutputContext|null $context
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
