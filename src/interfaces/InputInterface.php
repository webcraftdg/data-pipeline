<?php
/**
 * InputInterface.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\interfaces
 */
namespace webcraftdg\dataPipeline\interfaces;

interface InputInterface
{

    /**
     * open
     *
     * @return void
     */
    public function open(): void;
    /**
     *  read
     *
     * @return iterable
     */
    public function read(): iterable;
    /**
     * close
     *
     * @return void
     */
    public function close(): void;
}
