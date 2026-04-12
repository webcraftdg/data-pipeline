<?php
/**
 * XmlWriter.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\io\writers
 */
namespace webcraftdg\dataPipeline\io\writers;

use webcraftdg\dataPipeline\interfaces\DataWriterInterface;
use webcraftdg\dataPipeline\configs\PipelineConfig;
use webcraftdg\dataPipeline\contexts\OutputContext;
use XMLWriter as GlobalXMLWriter;
use InvalidArgumentException;

class XmlWriter implements DataWriterInterface
{
    /**
     * @var xmlWriter
     */
    private GlobalXMLWriter $xmlWriter;
    private string|null $path;


    /**
     * constructor
    *
    * @param  PipelineConfig $config
    * @param  array          $options
    */
    public function __construct(private PipelineConfig $config, private array $options = [])
    {
        $xmlWriter = new GlobalXMLWriter();
        $this->xmlWriter = $xmlWriter;
        $this->path = ($this->options['path']) ?? null;
    }

     /**
     * open
     *
     * @param  array $params
     *
     * @return void
     */
    public function open(): void
    {
        
        if ($this->path === null) {
            throw new InvalidArgumentException('XmlWriter params "path" not found');
        }
        $this->xmlWriter->openUri($this->path);
        $this->xmlWriter->startDocument('1.0', 'UTF-8');
        $this->xmlWriter->startElement('export');
        $this->xmlWriter->writeAttribute('name', $this->config->name);
        $this->xmlWriter->writeAttribute('version', $this->config->version);
        $this->xmlWriter->writeAttribute('generated_at', date('c'));
        $this->xmlWriter->setIndent(true);          // Active l'indentation
        $this->xmlWriter->startElement('records');
        $this->xmlWriter->setIndentString('  ');
    }

    /**
     * write
     *
     * @param  array              $row
     * @param  OutputContext|null $context
     *
     * @return void
     */
    public function write(array $row, ?OutputContext $context = null): void
    {
        if (empty($row) === false) {
            $this->xmlWriter->startElement('record');
            foreach ($row as $field => $value) {
                $this->xmlWriter->startElement('field');
                $this->xmlWriter->writeAttribute('name', $field);
                $this->xmlWriter->text($value);
                $this->xmlWriter->endElement();
            }
            $this->xmlWriter->endElement();
        }
    }

    /**
     * close
     *
     * @return void
     */
    public function close(): void
    {
        $this->xmlWriter->endElement(); // rows
        $this->xmlWriter->endElement(); // </export>
        $this->xmlWriter->endDocument();
        $this->xmlWriter->flush();
    }
}
