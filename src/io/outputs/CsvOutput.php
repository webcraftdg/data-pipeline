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
use webcraftdg\dataPipeline\interfaces\RuntimeContextInterface;
use webcraftdg\dataPipeline\interfaces\ValidateRulesInterface;

class CsvOutput implements OutputInterface, ValidateRulesInterface
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
     * @param  PipelineConfig               $config
     * @param  RuntimeContextInterface|null $context
     */
    public function __construct(
        private PipelineConfig $config,
        ?RuntimeContextInterface $context = null)
    {
        $this->writer = new CsvWriter($this->config, $this->config->target->getOptions());
    }
 

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
            'delimiter' => ['required' => false, 'type' => 'string'],
            'enclosure' => ['required' => false, 'type' => 'string'],
            'escape' => ['required' => false, 'type' => 'string'],
            'eol' => ['required' => false, 'type' => 'string'],
            'batchSize' => ['required' => false, 'type' => 'integer'],
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
