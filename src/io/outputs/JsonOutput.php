<?php
/**
 * JsonOutput.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\io\outputs
 */
namespace webcraftdg\dataPipeline\io\outputs;

use webcraftdg\dataPipeline\interfaces\OutputInterface;
use webcraftdg\dataPipeline\io\writers\JsonWriter;
use webcraftdg\dataPipeline\configs\PipelineConfig;
use webcraftdg\dataPipeline\interfaces\RuntimeContextInterface;
use webcraftdg\dataPipeline\interfaces\ValidateRulesInterface;

class JsonOutput implements OutputInterface, ValidateRulesInterface
{
    /**
     * $writer
     *
     * @var JsonWriter
     */
    private  JsonWriter $writer;

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
        $this->writer = new JsonWriter($this->config, $this->config->target->getOptions());
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
