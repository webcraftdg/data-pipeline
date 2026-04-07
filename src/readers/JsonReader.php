<?php
/**
 * JsonReader.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\readers
 */
namespace webcraftdg\dataPipeline\readers;


use webcraftdg\dataPipeline\interfaces\DataReaderInterface;
use Exception;

class JsonReader implements DataReaderInterface
{

    private int $batchSize = 250;
    private array $records = [];

    /**
     * open
     *
     * @param  string $filePath
     * @param  array  $options
     *
     * @return void
     */
    public function open(string $filePath, array $options = []): void
    {
        try {
            try {
                $this->batchSize = ($options['batchSize']) ?? $this->batchSize;
                $data = json_decode(file_get_contents($filePath));
                $this->records = ($data['records']) ?? [];
            } catch (Exception $e)  {
                $this->records = [];
            }
        } catch (Exception $e)  {
            throw  $e;
        }
    }

    /**
     * read
     *
     * @return iterable
     */
    public function read(): iterable
    {
         try {
            $batch = [];
            $indexBatch = 0;

            foreach($this->records as $record) {
                $batch[] = $this->getRowValues(($record['fields']) ?? []);
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

        } catch (Exception $e)  {
            throw  $e;
        }
    }

    /**
     * Undocumented function
     *
     * @param  array $fields
     *
     * @return array
     */
    public function getRowValues(array $fields) : array
    {
        try {
            $row = [];
            foreach($fields as $field) {
                $name = ($field['label']) ?? null;
                $value = ($field['value']) ?? '';
                if ($name === null) {
                    continue;
                }
                $row[$name] = $value;
            }
            return $row;
        } catch (Exception $e)  {
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
            $this->records = [];
        } catch (Exception $e)  {
            throw  $e;
        }
    }
}
