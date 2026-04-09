<?php
/**
 * NDJsonReader.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\io\inputs
 */
namespace webcraftdg\dataPipeline\io\inputs;


use webcraftdg\dataPipeline\interfaces\InputInterface;
use InvalidArgumentException;
use Exception;

class NDJsonInput implements InputInterface
{

    private $handle;
    private int $batchSize = 250;
    private $mapColumns = [];

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
     * @param  string $filePath
     * @param  array  $options
     *
     * @return void
     */
    public function open(string $filePath, array $options = []): void
    {
        try {
            $this->batchSize = ($options['batchSize']) ?? $this->batchSize;
            $this->handle = fopen($filePath, 'rb');
            $metaLine = fgets($this->handle);
            if ($metaLine !== false) {
                try {
                    $metas = json_decode($metaLine);
                    if (isset($metas['_type']) === true && $metas['_type'] == 'metas') {
                        $this->mapColumns = ($metas['columns']) ?? [];
                    }
                } catch(Exception $e) {
                    throw new InvalidArgumentException('NDJsonReader Meta of file not found: check your file is NDJSON ! ');
                }
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
            while (($line = fgets($this->handle)) !== false) {
                $row = json_decode($line);
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
            fclose($this->handle);
        } catch (Exception $e)  {
            throw  $e;
        }
    }
}
