<?php
/**
 * XmlInput.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\io\inputs
 */
namespace webcraftdg\dataPipeline\io\inputs;

use webcraftdg\dataPipeline\interfaces\InputInterface;
use XMLReader as GlobalXMLReader;
use Exception;

class XmlInput implements InputInterface
{

    /**
     * $xmlReader
     *
     * @var GlobalXMLReader
     */
    private GlobalXMLReader $xmlReader;
    
    /**
     * $batchSize
     *
     * @var int
     */
    private $batchSize = 250;


    /**
     * constructor
     *
     * @param  array          $options
     */
    public function __construct(private array $options = [])
    {
        $this->xmlReader = new GlobalXMLReader();
    }

    /**
     * read
     *
     * @return void
     */
    public function open(): void
    {
        $filePath = ($this->options['path']) ?? '';
        $this->xmlReader->open($filePath);
        $this->batchSize = ($this->options['batchSize']) ?? $this->batchSize;
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

        while ($this->xmlReader->read() === true) {
                $nodeType = $this->xmlReader->nodeType;
                $nodeName = $this->xmlReader->name;
                if (
                    $nodeType === GlobalXMLReader::ELEMENT
                    && $nodeName === 'record'
                ) {
                    $batch[] = $this->getRowValues();
                    $indexBatch ++;
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
    }

    /**
     * get row values
     *
     * @return array
     */
    public function getRowValues() : array
    {
        $row = [];
        $depth = $this->xmlReader->depth;

        while ($this->xmlReader->read()) {
            // fin du record
            $nodeType = $this->xmlReader->nodeType;
            $nodeName = $this->xmlReader->name;
            if ($nodeType === GlobalXMLReader::END_ELEMENT 
                && $nodeName === 'record' 
                && $this->xmlReader->depth === $depth) {
                break;
            }

            if ($nodeType === GlobalXMLReader::ELEMENT && $nodeName === 'field') {
                $name = $this->xmlReader->getAttribute('name');
                if ($name === null || $name === '') {
                    continue;
                }

                $value = $this->readFieldValue();
                $row[$name] = $value;
            }
        }
        return $row;
    }

    /**
     * read field value
     *
     * @return string
     */
    private function readFieldValue(): string
    {

        $value = '';

        while ($this->xmlReader->read()) {
            if ($this->xmlReader->nodeType === GlobalXMLReader::TEXT || $this->xmlReader->nodeType === GlobalXMLReader::CDATA) {
                $value .= $this->xmlReader->value;
            }
            if (
                $this->xmlReader->nodeType === GlobalXMLReader::END_ELEMENT
                && $this->xmlReader->name === 'field'
            ) {
                break;
            }
        }

        return $value; 
    }

    /**
     * close
     *
     * @return void
     */
    public function close(): void
    {
        $this->xmlReader->close();
    }
}
