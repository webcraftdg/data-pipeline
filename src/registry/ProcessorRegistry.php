<?php
/**
 * ProcessorRegistry.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\registry
 */
namespace webcraftdg\dataPipeline\registry;

use webcraftdg\dataPipeline\interfaces\ProcessorInterface;

use webcraftdg\dataPipeline\configs\PipelineConfig;
use webcraftdg\dataPipeline\configs\ProcessorConfig;
use webcraftdg\dataPipeline\exceptions\RegistryException;

class ProcessorRegistry
{
    /** @var array[<string, string> */
    private array $map = [];


    /**
     * @param array $processors
     */
    public function __construct(?array $processors = [])
    {
        foreach ($processors as $name => $processorClass) {
            $this->map[$name] = $processorClass;
        }
    }

    /**
     * register
     *
     * @param  string $name
     * @param  string $processorClass
     *
     * @return void
     */
    public function register(string $name, string $processorClass): void
    {
        $this->map[$name] = $processorClass;
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
     * create
     *
     * @param  PipelineConfig    $config
     *
     * @return ProcessorInterface
     */
    public function create(PipelineConfig $config): ProcessorInterface
    {
        $processor = null;
        if ($config->processor instanceof ProcessorConfig) {
            if (isset($this->map[$config->processor->name]) === false) {
                throw new RegistryException('Unknown processor "' . $config->processor->name . '".');
            }
            $class = $this->map[$config->processor->name];
            $processor = new $class($config->processor->options);
        }
        return $processor;
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
