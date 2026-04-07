<?php
/**
 * XmlWriter.php
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
use InvalidArgumentException;
use XMLWriter as GlobalXMLWriter;
use Exception;
use Yii;

class XmlWriter implements WriterInterface
{
    /**
     * @var xmlWriter
     */
    private GlobalXMLWriter $xmlWriter;
    private ImportConfig $config;
    private RecordFormatter $recordFormatter;
    /**
     * @var resource | false
     */
    private  $f;

     /**
     * __construct
     *
     * @param  ImportConfig $importConfig
     */
    public function __construct(ImportConfig $importConfig)
    {
        $xmlWriter = new GlobalXMLWriter();
        $this->xmlWriter = $xmlWriter;
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
                throw new InvalidArgumentException('XmlWriter params "path" not found');
            }
            $this->xmlWriter->openUri($path);
            $this->xmlWriter->startDocument('1.0', 'UTF-8');
            $this->xmlWriter->startElement('export');
            $this->xmlWriter->writeAttribute('name', $this->config->name);
            $this->xmlWriter->writeAttribute('dateCreate', date('c', strtotime($this->config->dateCreate)));
            $this->xmlWriter->writeAttribute('generated_at', date('c'));
            $this->xmlWriter->setIndent(true);          // Active l'indentation
            $this->xmlWriter->startElement('records');
            $this->xmlWriter->setIndentString('  ');
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
                $this->xmlWriter->startElement('fields');
                foreach ($row as $field => $item) {
                    $this->xmlWriter->startElement('field');
                    $this->xmlWriter->writeAttribute('columnId', ($item['columnId']) ?? 'notfound');
                    $this->xmlWriter->writeAttribute('name', ($item['name']) ?? $field);
                    $this->xmlWriter->writeAttribute('label', $field);
                    $value = ($item['value']) ?? '';
                    $this->xmlWriter->text($value);
                    $this->xmlWriter->endElement();
                }
                $this->xmlWriter->endElement();
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
            $this->xmlWriter->endElement(); // rows
            $this->xmlWriter->endElement(); // </export>
            $this->xmlWriter->endDocument();
            $this->xmlWriter->flush();
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
