<?php
/**
 * JsonInput.php
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

class JsonInput implements InputInterface, ValidateRulesInterface
{

    private int $batchSize = 250;
    private array $options;
    private string $path;
    private array $records = [];


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
        $data = json_decode(file_get_contents($this->path), true);
        $this->records = ($data['records']) ?? [];
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

        foreach($this->records as $record) {
            $record = ($record['record']) ?? [];
            if (empty($record) === false) {
                $batch[] = $record;
            }
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
     * close
     *
     * @return void
     */
    public function close(): void
    {
        $this->records = [];
    }
}
