<?php
/**
 * CsvWriter.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\io\outputs
 */
namespace webcraftdg\dataPipeline\io\outputs;

use webcraftdg\dataPipeline\interfaces\OutputInterface;
use webcraftdg\dataPipeline\io\writers\XmlWriter;
use InvalidArgumentException;
use Exception;

class XmlOutput implements OutputInterface
{
    /**
     * $writer
     *
     * @var XmlWriter
     */
    private  XmlWriter $writer;

 
    public function open(): void
    {
        try {
            $path = $writerContext->absolutePath ?? null;
            if ($path === null) {
                throw new InvalidArgumentException('CsvWriter params "path" not found');
            }
            $this->handle = fopen($path, 'w');
        } catch (Exception $e) {
            throw  $e;
        }
    }

 
    public function write(array $row): void
    {
        try {
            fputcsv($this->handle, $row, ';', '"', "\\", \PHP_EOL);
        } catch (Exception $e) {
            throw  $e;
        }
    }

    /**
     * close
     *
     * @return void
     */
    public function close(): void
    {
        try {
            $this->writer->close();
        } catch (Exception $e) {
            throw  $e;
        }
    }
}
