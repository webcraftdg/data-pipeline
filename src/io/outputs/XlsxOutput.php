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
use webcraftdg\dataPipeline\io\writers\XlsxWriter;
use InvalidArgumentException;
use Exception;
use webcraftdg\dataPipeline\configs\PipelineConfig;
use webcraftdg\dataPipeline\contexts\OutputContext;
use webcraftdg\dataPipeline\exceptions\OutputResult;

class XlsxOutput implements OutputInterface
{
    /**
     * $writer
     *
     * @var XlsxWriter
     */
    private  XlsxWriter $writer;

     /**
     * constructor
     *
     * @param  PipelineConfig $config
     * @param  array          $options
     */
    public function __construct(private PipelineConfig $config, private array $options = [])
    {
        $this->writer = new XlsxWriter($config, $options);
    }


    public function open(): void
    {
        try {
            $this->writer->open();
        } catch (Exception $e) {
            throw  $e;
        }
    }

 
    public function write(array $row, ?OutputContext $context = null): void
    {
        try {
            $this->writer->write($row, $context);
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
