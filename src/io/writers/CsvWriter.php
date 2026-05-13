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
use webcraftdg\dataPipeline\configs\ColumnMapping;
use webcraftdg\dataPipeline\configs\PipelineConfig;
use webcraftdg\dataPipeline\context\OutputContext;
use InvalidArgumentException;

class CsvWriter implements DataWriterInterface
{
    /**
     * @var $handle resource | false
     */
    private $handle;
    private $headerWritten = false;
    private string|null $path;
    private string $delimiter = ';';
    private string $enclosure = '"';
    private string $escape = '\\';
    private string $eol = '\n';
    private OutputContext $outputContext;


    /**
     * constructor
     *
     * @param  PipelineConfig $config
     * @param  array          $options
     */
    public function __construct(private PipelineConfig $config, private array $options = [])
    {
        $this->path = ($this->options['path']) ?? null;
        $this->delimiter = ($this->options['delimiter']) ?? $this->delimiter;
        $this->enclosure = ($this->options['enclosure']) ?? $this->delimiter;
        $this->escape = ($this->options['escape']) ?? $this->escape;
        $this->eol = ($this->options['eol']) ?? $this->eol;
        $this->outputContext = new OutputContext(
            colOffset: 1,
            rowOffset: 1,
            sectionName: 'onglet 1',
            headers: ($this->options['headers']) ?? []
        );
    }

    /**
     * open
     *
     * @return void
     */
    public function open(): void
    {
        if ($this->path === null) {
            throw new InvalidArgumentException('CsvWriter params "path" not found');
        }
        $this->handle = fopen($this->path, 'w');
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
        $this->addHeaders();
        fputcsv($this->handle, $row, $this->delimiter, $this->enclosure, $this->escape, $this->eol);
    }

    /**
     * add headers
     *
     * @return void
     */
    private function addHeaders(): void
    {

        if ($this->headerWritten === false) {
            $headers = $this->outputContext->headers;
            if (empty($headers) === true) {
                $headers = array_map(function(ColumnMapping $column) {
                    return $column->outputKey;
                }, $this->config->columns);
            }
            fputcsv($this->handle, $headers, $this->delimiter, $this->enclosure, $this->escape, $this->eol);
            $this->headerWritten = true;
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
