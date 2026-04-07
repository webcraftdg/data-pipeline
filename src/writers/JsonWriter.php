<?php
/**
 * JsonWriter.php
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
use fractalCms\importExport\pipeline\interfaces\RecordFormatter;
use fractalCms\importExport\pipeline\formatters\Record;
use fractalCms\importExport\models\ImportConfig;
use yii\helpers\Json;
use InvalidArgumentException;
use Exception;
use Yii;

class JsonWriter implements WriterInterface
{
    /**
     * @var resource | false
     */
    private  $handle;
    private ImportConfig $config;
    private RecordFormatter $recordFormatter;
    private bool $firstRecord = true;


    /**
     * __construct
     *
     * @param  ImportConfig $importConfig
     */
    public function __construct(ImportConfig $importConfig)
    {
        $this->config = $importConfig;
        $this->recordFormatter = new Record();
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
                throw new InvalidArgumentException('JsonWriter params "path" not found');
            }
            $meta =  [
                'configId' => $this->config->id,
                'name' => $this->config->name,
                'version' => $this->config->version,
                'dateCreate' => date('c', strtotime($this->config->dateCreate)),
                'generatedAt' => date('c'),
            ];
        
            $this->handle = fopen($path, 'w');
            fwrite($this->handle, '{'."\n");
            fputs($this->handle, '"metas":'.Json::encode($meta).",\n");
            fputs($this->handle, '"records":['."\n");
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
                $row = $this->recordFormatter->format($row, $this->config);
                if ($this->firstRecord === false) {
                    fputs($this->handle, ','."\n");
                }
                fputs($this->handle, Json::encode(['fields' => $this->prepareRow($row)]));
                $this->firstRecord = false;
            }
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * prepare row
     *
     * @param  array $rawRow
     *
     * @return array
     */
    protected function prepareRow(array $rawRow) : array
    {
         try {
            $fields = [];
            foreach ($rawRow as $fieldName => $item) {
                $field = [];
                $field['columnId'] = ($item['columnId']) ?? '';
                $field['name'] = ($item['name']) ?? '';
                $field['label'] = $fieldName;
                $field['value'] = ($item['value']) ?? '';
                $fields[] = $field;
            }
            return $fields;
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
            fputs($this->handle, "\n".']}');
            fclose($this->handle);
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
