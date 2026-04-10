<?php
/**
 * InputRegistry.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\registry
 */
namespace webcraftdg\dataPipeline\registry;

use webcraftdg\dataPipeline\interfaces\InputInterface;
use webcraftdg\dataPipeline\io\inputs\ArrayDataInput;
use webcraftdg\dataPipeline\io\inputs\ExcelInput;
use webcraftdg\dataPipeline\io\inputs\JsonInput;
use webcraftdg\dataPipeline\io\inputs\NDJsonInput;
use webcraftdg\dataPipeline\io\inputs\XmlInput;
use webcraftdg\dataPipeline\configs\PipelineConfig;
use webcraftdg\dataPipeline\builders\HeadersBuider;
use RuntimeException;

class InputRegistry
{

    /** @var array<string, string> */
    private array $map = [
        'xlsx' => ExcelInput::class,
        'xls' => ExcelInput::class,
        'csv' => ExcelInput::class,
        'json' => JsonInput::class,
        'ndjson' => NDJsonInput::class,
        'xml' => XmlInput::class,
        'array' => ArrayDataInput::class,
    ];

    /**
     * create
     *
     * @param  PipelineConfig $config
     *
     * @return InputInterface
     */
    public function create(PipelineConfig $config): InputInterface
    {
        if (isset($this->map[$config->source->name]) === false) {
            throw new RuntimeException('Unknown input "' . $config->source->name . '".');
        }
        if (isset($config->source->options['headers']) === false) {
            $config->source->options['headers'] = HeadersBuider::fromPipeline($config);
        }
        $class = $this->map[$config->source->name];
        return new $class($config->source->options);
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
