<?php
/**
 * NDJsonInput.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\io\inputs
 */
namespace webcraftdg\dataPipeline\io\inputs;

use webcraftdg\dataPipeline\configs\SourceConfig;
use webcraftdg\dataPipeline\interfaces\InputInterface;
use webcraftdg\dataPipeline\interfaces\RuntimeContextInterface;
use webcraftdg\dataPipeline\interfaces\ValidateRulesInterface;

class NDJsonInput implements InputInterface, ValidateRulesInterface
{

    private $handle;
    private $path;
    private array $options;
    private int $batchSize = 250;

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
        $this->path = ($this->options['path']) ?? '';
        $this->batchSize = ($this->options['batchSize']) ?? $this->batchSize;
    }

    /**
     * open
     *
     * @return void
     */
    public function open(): void
    {
        $this->handle = fopen($this->path, 'rb');
    }


    /**
     * rules
     *
     * @return array
     */
    public static function rules() : array
    {
        return [
            'path' => ['required' => true, 'type' => 'string'],
            'batchSize' => ['required' => false, 'type' => 'integer'],
        ];
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
        while (($line = fgets($this->handle)) !== false) {
            $row = json_decode($line, true);
            if (isset($row['_type']) === true && $row['_type'] == 'data') {
                unset($row['_type']);
                $batch[] = $row;
                $indexBatch++;
                    if ($indexBatch >= $this->batchSize) {
                    yield $batch;
                    $batch = [];
                    $indexBatch = 0;
                }
            }
        }
        if (empty($batch) === false) {
            yield $batch;
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
