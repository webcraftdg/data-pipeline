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
use webcraftdg\dataPipeline\interfaces\RuntimeContextInterface;
use webcraftdg\dataPipeline\interfaces\ValidateRulesInterface;

class XmlOutput implements OutputInterface, ValidateRulesInterface
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
     * @param  PipelineConfig               $config
     * @param  RuntimeContextInterface|null $context
     */
    public function __construct(
        private PipelineConfig $config,
        ?RuntimeContextInterface $context = null)
    {
        $this->writer = new XmlWriter($this->config, $this->config->target->getOptions());
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
     * rules
     *
     * @return array
     */
    public static function rules() : array
    {
        return [
            'path' => ['required' => false, 'type' => 'string'],
        ];
    }
 
    /**
     * write
     *
     * @param  array              $row
     *
     * @return void
     */
    public function write(array $row): void
    {
        $this->writer->write($row);
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
