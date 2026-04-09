<?php
/**
 * CsvWriter.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\io\writers
 */
namespace webcraftdg\dataPipeline\io\writers;

use webcraftdg\dataPipeline\interfaces\DataWriterInterface;
use webcraftdg\dataPipeline\exceptions\OutputResult;
use InvalidArgumentException;
use Exception;
use webcraftdg\dataPipeline\configs\ColumnMapping;
use webcraftdg\dataPipeline\configs\PipelineConfig;
use webcraftdg\dataPipeline\contexts\OutputContext;

class CsvWriter implements DataWriterInterface
{
    /**
     * @var $handle resource | false
     */
    private $handle;
    private $headerWritten = false;


      /**
     * constructor
     *
     * @param  \webcraftdg\dataPipeline\configs\PipelineConfig $config
     */
    public function __construct(private PipelineConfig $config, private array $options = [])
    {
    }

    /**
     * open
     *
     * @return void
     */
    public function open(): void
    {
        try {
            $path = ($this->options['path']) ?? null;
            if ($path === null) {
                throw new InvalidArgumentException('CsvWriter params "path" not found');
            }
            $this->handle = fopen($path, 'w');
        } catch (Exception $e) {
            throw  $e;
        }
    }

    /**
     * write
     *
     * @param  array              $row
     * @param  OutputContext|null $context
     *
     * @return void
     */
    public function write(array $row, ?OutputContext $context = null): void
    {
        try {
            $this->addHeaders($context);
            fputcsv($this->handle, $row, ';', '"', "\\", \PHP_EOL);
        } catch (Exception $e) {
            throw  $e;
        }
    }

    /**
     * add headers
     *
     * @param  OutputContext|null $context
     *
     * @return void
     */
    private function addHeaders(?OutputContext $context = null): void
    {

        try {
            if ($this->headerWritten === false) {
                $headers = ($context !== null && empty($context->headers) === false) ? $context->headers : [];
                if (empty($headers) === true) {
                    $headers = array_map(function(ColumnMapping $column) {
                        return $column->outputKey;
                    }, $this->config->columns);
                }
                fputcsv($this->handle, $headers, ';', '"', "\\", \PHP_EOL);
                $this->headerWritten = true;
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
