<?php
/**
 * OutputRegistry.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\registry
 */
namespace webcraftdg\dataPipeline\registry;

use webcraftdg\dataPipeline\io\outputs\CsvOutput;
use webcraftdg\dataPipeline\io\outputs\JsonOutput;
use webcraftdg\dataPipeline\io\outputs\NDJsonOutput;
use webcraftdg\dataPipeline\io\outputs\XlsxOutput;
use webcraftdg\dataPipeline\io\outputs\XmlOutput;
use webcraftdg\dataPipeline\interfaces\OutputInterface;
use webcraftdg\dataPipeline\configs\PipelineConfig;
use RuntimeException;

class OutputRegistry
{
   /** @var array<string, string> */
    private array $map = [
        'csv' => CsvOutput::class,
        'json' => JsonOutput::class,
        'ndjson' => NDJsonOutput::class,
        'xlsx' => XlsxOutput::class,
        'xml' => XmlOutput::class,
    ];

    /**
     * create
     *
     * @param  PipelineConfig  $config
     *
     * @return OutputInterface
     */
    public function create(PipelineConfig $config): OutputInterface
    {
        if (isset($this->map[$config->target->name]) === false) {
            throw new RuntimeException('Unknown input "' . $config->target->name . '".');
        }
        $class = $this->map[$config->target->name];
        return new $class($config, $config->target->options);
    }

       /**
     * has
     *
     * @param  string $name
     *
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->map[$name]);
    }
}
