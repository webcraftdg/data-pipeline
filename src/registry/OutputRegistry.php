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
use webcraftdg\dataPipeline\exceptions\RegistryException;

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
     * @param array $outputs
     */
    public function __construct(?array $outputs = [])
    {
        foreach ($outputs as $name => $output) {
            $this->map[$name] = $output;
        }
    }

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
            throw new RegistryException('Unknown output "' . $config->target->name . '".');
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

     /**
     * get class
     *
     * @param  string $name
     *
     * @return string | null
     */
    public function getClass(string $name) : string | null
    {
        return ($this->map[$name]) ?? null;
    }
}
