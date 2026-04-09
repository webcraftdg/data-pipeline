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
use webcraftdg\dataPipeline\formatters\RowFileFormatter;
use InvalidArgumentException;
use Exception;
use webcraftdg\dataPipeline\contexts\OutputContext;

class JsonWriter implements DataWriterInterface
{
    /**
     * @var resource | false
     */
    private  $handle;
    private RowFileFormatter $rowJsonFormatter;
    private bool $firstRecord = true;


    /**
     * constructor
     *
     * @param  \webcraftdg\dataPipeline\configs\PipelineConfig $config
     */
    public function __construct(private PipelineConfig $config, private array $options = [])
    {
        $this->rowJsonFormatter = new RowFileFormatter();
    }

    public function open(): void
    {
        try {
            $path = ($this->options['path']) ?? null;
            if ($path === null) {
                throw new InvalidArgumentException('JsonWriter params "path" not found');
            }
            $meta =  [
                'name' => $this->config->name,
                'version' => $this->config->version,
                'generatedAt' => date('c'),
            ];
        
            $this->handle = fopen($path, 'w');
            fwrite($this->handle, '{'."\n");
            fputs($this->handle, '"metas":'.json_encode($meta).",\n");
            fputs($this->handle, '"records":['."\n");
        } catch (Exception $e) {
            throw  $e;
        }
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
        try {
            if (empty($row) === false) {
                $row = $this->rowJsonFormatter->format($row, $this->config);
                if ($this->firstRecord === false) {
                    fputs($this->handle, ','."\n");
                }
                fputs($this->handle, json_encode(['fields' => $this->prepareRow($row)]));
                $this->firstRecord = false;
            }
        } catch (Exception $e) {
            throw  $e;
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
         try {
            $fields = [];
            foreach ($rawRow as $fieldName => $item) {
                $field = [];
                $field['name'] = ($item['name']) ?? '';
                $field['label'] = $fieldName;
                $field['value'] = ($item['value']) ?? '';
                $fields[] = $field;
            }
            return $fields;
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
            fputs($this->handle, "\n".']}');
            fclose($this->handle);
        } catch (Exception $e) {
            throw  $e;
        }
    }
}
