<?php
/**
 * CsvInput.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\io\inputs
 */
namespace webcraftdg\dataPipeline\io\inputs;

use webcraftdg\dataPipeline\interfaces\InputInterface;
use webcraftdg\dataPipeline\interfaces\ValidateRulesInterface;
use webcraftdg\dataPipeline\configs\SourceConfig;
use webcraftdg\dataPipeline\interfaces\RuntimeContextInterface;
use webcraftdg\dataPipeline\rules\FileRules;
use InvalidArgumentException;
use yii\helpers\ArrayHelper;

class CsvInput implements InputInterface, ValidateRulesInterface
{


    
    private $handle;
    private array $options;
    private array $headers = [];
    private int $batchSize = 250;
    private bool $hasHeader = true;
    private string $delimiter = ';';
    private string $enclosure = '"';
    private string $inputEncoding = 'ISO-8859-1';
    private ?RuntimeContextInterface $context;

    /**
     * constructor
     *
     * @param  SourceConfig                 $config
     * @param  RuntimeContextInterface|null $context
     */
    public function __construct(
        private SourceConfig $config,
        ?RuntimeContextInterface $context = null
    )
    {
        $this->options = $this->config->getOptions();
        $this->headers = ($this->options['headers']) ?? [];
        $this->batchSize = ($this->options['batchSize']) ?? $this->batchSize;
        $this->delimiter = ($this->options['delimiter']) ?? $this->delimiter;
        $this->enclosure = ($this->options['enclosure']) ?? $this->enclosure;
        $this->hasHeader = ($this->options['hasHeader']) ?? $this->hasHeader;
        $this->inputEncoding = ($this->options['inputEncoding']) ?? $this->inputEncoding;
        $this->context = $context;
    }


       /**
     * rules
     *
     * @return array
     */
    public static function rules() : array
    {
        return ArrayHelper::merge(FileRules::rulesHeader(), FileRules::rulesCsv(), FileRules::rulesPath(),FileRules::rulesBatchSize());
    }

    /**
     * open
     *
     * @return void
     */
    public function open(): void
    {
        $path = ($this->options['path']) ?? '';
        if (!$path || !is_file($path)) {
            throw new InvalidArgumentException('CsvInput option "path" is invalid.');
        }
        $this->handle = fopen($path, 'rb');
    }

    /**
     * read
     *
     * @return iterable
     */
    public function read(): iterable
    {
        $batch = [];
        $indexBatch = 0;
        if ($this->hasHeader && empty($this->headers) === true) {
            $this->headers = fgetcsv($this->handle, 0, $this->delimiter, $this->enclosure) ?: [];
        }

        while (($row = fgetcsv($this->handle, 0, $this->delimiter, $this->enclosure)) !== false) {
            if ($this->hasHeader) {
                $row = array_combine($this->headers, $row) ?: [];
            }
            $row = $this->manageEncoding($row, $this->inputEncoding);
            $batch[] = $row;
            $indexBatch ++;
            if ($indexBatch >= $this->batchSize) {
                yield $batch;
                $batch = [];
                $indexBatch = 0;
            }
        }
        if (empty($batch) === false) {
            yield $batch;
        }
    }

    /**
     * manage encoding
     *
     * @param  array  $row
     * @param  string $encoding
     *
     * @return array
     */
    private function manageEncoding(array $row, string $encoding) : array
    {
     return array_map(
        fn ($value) => is_string($value)
        ? mb_convert_encoding($value, 'UTF-8', $encoding)
        : $value, $row);
    }

    /**
     * close
     *
     * @return void
     */
    public function close(): void
    {
        if (is_resource($this->handle)) {
            fclose($this->handle);
        }

        $this->handle = null;
        $this->headers = [];
    }
}
