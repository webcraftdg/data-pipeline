<?php
/**
 * CsvOutput.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\io\outputs
 */
namespace webcraftdg\dataPipeline\io\outputs;

use webcraftdg\dataPipeline\interfaces\OutputInterface;
use webcraftdg\dataPipeline\io\writers\CsvWriter;
use webcraftdg\dataPipeline\configs\PipelineConfig;
use webcraftdg\dataPipeline\contexts\OutputContext;

class CsvOutput implements OutputInterface
{
    /**
     * $writer
     *
     * @var CsvWriter
     */
    private  CsvWriter $writer;


    /**
     * constructor
     *
     * @param  PipelineConfig $config
     * @param  array          $options
     */
    public function __construct(private PipelineConfig $config, private array $options = [])
    {
        $this->writer = new CsvWriter($config, $options);
    }
 

    public function open(): void
    {
        $this->writer->open();
    }

 
    /**
     * write
     *
     * @param  array              $row
     * @param  OutputContext|null $context
     *
     * @return void
     */
    public function write(array $row, ?OutputContext $context = null): void
    {
        $this->writer->write($row, $context);
    }

    /**
     * close
     *
     * @return void
     */
    public function close(): void
    {
        $this->writer->close();
    }
}
