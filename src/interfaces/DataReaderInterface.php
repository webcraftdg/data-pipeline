<?php
/**
 * DataReaderInterface.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\interfaces
 */
namespace webcraftdg\dataPipeline\interfaces;

interface DataReaderInterface
{

    /**
     * open
     *
     * @param  array        $options
     *
     * @return void
     */
    public function open(array $options): void;
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