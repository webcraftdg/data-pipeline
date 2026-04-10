<?php
/**
 * XmlOutput.php
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
use webcraftdg\dataPipeline\configs\PipelineConfig;
use webcraftdg\dataPipeline\contexts\OutputContext;

class XmlOutput implements OutputInterface
{
    /**
     * $writer
     *
     * @var XmlWriter
     */
    private  XmlWriter $writer;


       /**
     * constructor
     *
     * @param  PipelineConfig $config
     * @param  array          $options
     */
    public function __construct(private PipelineConfig $config, private array $options = [])
    {
        $this->writer = new XmlWriter($config, $options);
    }

 
    /**
     * open
     *
     * @return void
     */
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
