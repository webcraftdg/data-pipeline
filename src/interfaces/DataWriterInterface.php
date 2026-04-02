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

use fractalCms\importExport\runtime\contexts\Writer as WriterContext;
use fractalCms\importExport\io\exports\writers\WriteTarget;

interface DataWriterInterface
{
    /**
     * open
     *
     * @param  array] $params
     *
     * @return void
     */
    public function open(WriterContext $writerContext): void;

    /**
     * @param WriteTarget $target
     * @param array $row
     * @return void
     */
    public function write(WriteTarget $target, array $row): void;

    /**
     * close
     *
     * @return void
     */
    public function close(WriterContext $writerContext): void;
}
