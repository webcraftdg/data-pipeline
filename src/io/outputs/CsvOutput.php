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
use webcraftdg\dataPipeline\rules\FileRules;
use yii\helpers\ArrayHelper;

class CsvOutput implements OutputInterface, ValidateRulesInterface
{
    /**
     * $writer
     *
     * @var CsvWriter
     */
    private  CsvWriter $writer;
    private ?RuntimeContextInterface $context;

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
        $this->context = $context;
    }
 

    /**
     * rules
     *
     * @return array
     */
    public static function rules() : array
    {
        return ArrayHelper::merge(FileRules::rulesHeader(), FileRules::rulesCsv(), FileRules::rulesPath());
    }

    /**
     * Open
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
