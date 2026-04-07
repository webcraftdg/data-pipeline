<?php
/**
 * CsvWriter.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\writers
 */
namespace webcraftdg\dataPipeline\writers;

use fractalCms\importExport\io\interfaces\Writer as WriterInterface;
use fractalCms\importExport\runtime\contexts\Writer as WriterContext;
use fractalCms\importExport\io\exports\writers\WriteTarget;
use InvalidArgumentException;
use Exception;
use Yii;

class CsvWriter implements WriterInterface
{
    /**
     * @var $handle resource | false
     */
    private  $handle;

    /**
     * open
     *
     * @param  array $params
     *
     * @return void
     */
    public function open(WriterContext $writerContext): void
    {
        try {
            $path = $writerContext->absolutePath ?? null;
            if ($path === null) {
                throw new InvalidArgumentException('CsvWriter params "path" not found');
            }
            $this->handle = fopen($path, 'w');
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * @param WriteTarget $target
     * @param array $row
     * @return void
     * @throws Exception
     */
    public function write(WriteTarget $target, array $row): void
    {
        try {
            fputcsv($this->handle, $row, ';', '"', "\\", \PHP_EOL);
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * close
     *
     * @return void
     */
    public function close(WriterContext $writerContext): void
    {
        try {
            fclose($this->handle);
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
