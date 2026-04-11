<?php
/**
 * JsonWriter.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\io\writers
 */
namespace webcraftdg\dataPipeline\io\writers;

use webcraftdg\dataPipeline\configs\ColumnMapping;
use webcraftdg\dataPipeline\configs\PipelineConfig;
use webcraftdg\dataPipeline\contexts\OutputContext;
use webcraftdg\dataPipeline\interfaces\DataWriterInterface;
use InvalidArgumentException;

class NDJsonWriter implements DataWriterInterface
{
    /**
     * @var resource | false
     */
    private  $handle;
    private string|null $path;


    /**
     * constructor
     *
     * @param  \webcraftdg\dataPipeline\configs\PipelineConfig $config
     * @param  array                                           $options
     */
    public function __construct(private PipelineConfig $config, private array $options = [])
    {
        $this->path = ($this->options['path']) ?? null;
    }

    /**
     * open
     *
     * @param  array $params
     *
     * @return void
     */
    public function open(): void
    {
        if ($this->path === null) {
            throw new InvalidArgumentException('NDJsonWriter params "path" not found');
        }
        $conlumnsMappings = array_map(function(ColumnMapping $column) {
                    return ['inputKey' => $column->inputKey, 'outputKey' => $column->outputKey];
                }, $this->config->columns);
        $row =  [
            '_type' => 'metas',
            'name' => $this->config->name,
            'version' => $this->config->version,
            'columns' => $conlumnsMappings,
            'generatedAt' => date('c'),
        ];
        $this->handle = fopen($this->path, 'w');
        fwrite($this->handle, json_encode($row).PHP_EOL);
    }


    
    /**
     * write
     *
     * @param  array                                                $row
     * @param  \webcraftdg\dataPipeline\contexts\OutputContext|null $context
     *
     * @return void
     */
    public function write(array $row, ?OutputContext $context = null): void
    {
        if (empty($row) === false) {
            $row = array_merge(['_type' => 'data'], $row);
            fwrite($this->handle, json_encode($row).PHP_EOL);
        }
    }

    /**
     * close
     *
     * @return void
     */
    public function close(): void
    {
        fclose($this->handle);
    }
}
