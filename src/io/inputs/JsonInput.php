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


use webcraftdg\dataPipeline\interfaces\InputInterface;
use Exception;

class JsonInput implements InputInterface
{

    private int $batchSize = 250;
    private array $records = [];


    /**
     * constructor
     *
     * @param  array          $options
     */
    public function __construct(private array $options = [])
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
            $filePath = ($this->options['path']) ?? '';
            $this->batchSize = ($this->options['batchSize']) ?? $this->batchSize;
            $data = json_decode(file_get_contents($filePath), true);
            $this->records = ($data['records']) ?? [];
        } catch (Exception $e)  {
            $this->records = [];
        }
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
