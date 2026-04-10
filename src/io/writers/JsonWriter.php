<?php
/**
 * JsonWriter.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\io\writers
 */
namespace webcraftdg\dataPipeline\io\writers;


use webcraftdg\dataPipeline\configs\PipelineConfig;
use webcraftdg\dataPipeline\interfaces\DataWriterInterface;
use webcraftdg\dataPipeline\configs\ColumnMapping;
use webcraftdg\dataPipeline\contexts\OutputContext;
use InvalidArgumentException;

class JsonWriter implements DataWriterInterface
{
    /**
     * @var resource | false
     */
    private  $handle;
    private bool $firstRecord = true;


    /**
     * constructor
     *
     * @param  \webcraftdg\dataPipeline\configs\PipelineConfig $config
     */
    public function __construct(private PipelineConfig $config, private array $options = [])
    {
    }

    public function open(): void
    {
        $path = ($this->options['path']) ?? null;
        if ($path === null) {
            throw new InvalidArgumentException('JsonWriter params "path" not found');
        }
        $conlumnsMappings = array_map(function(ColumnMapping $column) {
        return ['inputKey' => $column->inputKey, 'outputKey' => $column->outputKey];
        }, $this->config->columns);
        $meta =  [
            'name' => $this->config->name,
            'version' => $this->config->version,
            'columns' => $conlumnsMappings,
            'generatedAt' => date('c'),
        ];
    
        $this->handle = fopen($path, 'w');
        fwrite($this->handle, '{'."\n");
        fputs($this->handle, '"metas":'.json_encode($meta).",\n");
        fputs($this->handle, '"records":['."\n");
    }


    /**
     * write
     *
     * @param  array                                            $row
     * @param  OutputContext|null                               $context
     *
     * @return void
     */
    public function write(array $row, ?OutputContext $context = null): void
    {
        if (empty($row) === false) {
            if ($this->firstRecord === false) {
                fputs($this->handle, ','."\n");
            }
            fputs($this->handle, json_encode(['record' => $row]));
            $this->firstRecord = false;
        }
    }

    /**
     * prepare row
     *
     * @param  array $rawRow
     *
     * @return array
     */
    protected function prepareRow(array $rawRow) : array
    {
        $fields = [];
        foreach ($rawRow as $fieldName => $item) {
            $field = [];
            $field['name'] = ($item['name']) ?? '';
            $field['label'] = $fieldName;
            $field['value'] = ($item['value']) ?? '';
            $fields[] = $field;
        }
        return $fields;
    }

    /**
     * close
     *
     * @return void
     */
    public function close(): void
    {
        fputs($this->handle, "\n".']}');
        fclose($this->handle);
    }
}
