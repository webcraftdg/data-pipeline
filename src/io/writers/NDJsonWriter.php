<?php
/**
 * JsonWriter.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\io\writers
 */
namespace webcraftdg\dataPipeline\io\writers;

use fractalCms\importExport\io\interfaces\Writer as WriterInterface;
use fractalCms\importExport\runtime\contexts\Writer as WriterContext;
use fractalCms\importExport\io\exports\writers\WriteTarget;
use fractalCms\importExport\pipeline\interfaces\RecordFormatter;
use fractalCms\importExport\pipeline\formatters\Record;
use fractalCms\importExport\models\ImportConfig;
use fractalCms\importExport\models\ImportConfigColumn;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use InvalidArgumentException;
use Exception;
use Yii;

class NDJsonWriter implements WriterInterface
{
    /**
     * @var resource | false
     */
    private  $handle;
    private ImportConfig $config;
    private RecordFormatter $recordFrormatter;
    private bool $firstRecord = true;


    /**
     * __construct
     *
     * @param  ImportConfig $importConfig
     */
    public function __construct(ImportConfig $importConfig)
    {
        $this->config = $importConfig;
        $this->recordFrormatter = new Record();
    }

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
                throw new InvalidArgumentException('NDJsonWriter params "path" not found');
            }
            $columnsDbQuery = $this->config->getImportColumns();
            $columns = [];
            /**
             * @var ImportConfigColumn $columnDb
             */
            foreach($columnsDbQuery->each() as $columnDb) {
                $columns[] = [
                    'name' => $columnDb->source,
                    'label' => $columnDb->target
                ];
            }
            $row =  [
                '_type' => 'metas',
                'configId' => $this->config->id,
                'name' => $this->config->name,
                'version' => $this->config->version,
                'columns' => $columns,
                'dateCreate' => date('c', strtotime($this->config->dateCreate)),
                'generatedAt' => date('c'),
            ];
            $this->handle = fopen($path, 'w');
            fwrite($this->handle, Json::encode($row).PHP_EOL);
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
            if (empty($row) === false) {
                $row = ArrayHelper::merge(['_type' => 'data'], $row);
                fwrite($this->handle, Json::encode($row).PHP_EOL);
            }
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
