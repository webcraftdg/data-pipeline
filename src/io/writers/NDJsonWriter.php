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

use InvalidArgumentException;
use Exception;
use webcraftdg\dataPipeline\configs\PipelineConfig;
use webcraftdg\dataPipeline\contexts\OutputContext;
use webcraftdg\dataPipeline\formatters\RowFileFormatter;
use webcraftdg\dataPipeline\interfaces\DataWriterInterface;

class NDJsonWriter implements DataWriterInterface
{
    /**
     * @var resource | false
     */
    private  $handle;


    /**
     * constructor
     *
     * @param  \webcraftdg\dataPipeline\configs\PipelineConfig $config
     * @param  array                                           $options
     */
    public function __construct(private PipelineConfig $config, private array $options = [])
    {
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
        try {
            $path = ($this->options['path']) ?? null;
            if ($path === null) {
                throw new InvalidArgumentException('NDJsonWriter params "path" not found');
            }
            $row =  [
                '_type' => 'metas',
                'name' => $this->config->name,
                'version' => $this->config->version,
                'generatedAt' => date('c'),
            ];
            $this->handle = fopen($path, 'w');
            fwrite($this->handle, json_encode($row).PHP_EOL);
        } catch (Exception $e) {
            throw  $e;
        }
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
        try {
            if (empty($row) === false) {
                $row = array_merge(['_type' => 'data'], $row);
                fwrite($this->handle, json_encode($row).PHP_EOL);
            }
        } catch (Exception $e) {
            throw  $e;
        }
    }

    /**
     * close
     *
     * @return void
     */
    public function close(): void
    {
        try {
            fclose($this->handle);
        } catch (Exception $e) {
            throw  $e;
        }
    }
}
