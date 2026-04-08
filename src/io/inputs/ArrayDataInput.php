<?php
/**
 * ArrayDataInput.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\io\inputs
 */
namespace webcraftdg\dataPipeline\io\inputs;

use webcraftdg\dataPipeline\interfaces\InputCountableInterface;
use InvalidArgumentException;
use Exception;

class ArrayDataInput implements InputCountableInterface
{

    private array $rows;
    private int $batchSize = 200;

    public function __construct(private array $options = [])
    {
        $this->rows = ($options['rows']) ?? null;
        if ($this->rows === null) {
            throw new InvalidArgumentException('ArrayExportData excepted params "rows"');
        }
        $this->batchSize = ($options['batchSize']) ?? $this->batchSize;
    }

    /**
     * open
     *
     * @param  array $options
     *
     * @return void
     */
   public function open(): void
    {
    }

    /**
     * @return iterable
     * @throws Exception
     */
    public function read(): iterable
    {
        try {
            $indexBatch = 0;
            $batch = [];
            foreach ($this->rows as $row) {
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
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @return int
     * @throws \yii\db\Exception
     */
    public function count() : int
    {
        try {
            return count($this->rows);
        } catch (Exception $e) {
            throw $e;
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
            //Not used here
        } catch (Exception $e) {
            throw $e;
        }
    }
}
