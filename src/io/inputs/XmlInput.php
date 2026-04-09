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
        try {
            $filePath = ($this->options['path']) ?? '';
            $this->xmlReader->open($filePath);
            $this->batchSize = ($this->options['batchSize']) ?? $this->batchSize;

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
    
            while ($this->xmlReader->read() === true) {
                    if (
                        $this->xmlReader->nodeType === GlobalXMLReader::ELEMENT
                        && $this->xmlReader->name === 'fields'
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

        } catch (Exception $e)  {
            throw  $e;
        }
    }

    /**
     * get row values
     *
     * @return array
     */
    public function getRowValues() : array
    {
        try {
            $row = [];
            $depth = $this->xmlReader->depth;

            while ($this->xmlReader->read()) {
                // fin du record
                if ($this->xmlReader->nodeType === GlobalXMLReader::END_ELEMENT 
                    && $this->xmlReader->name === 'fields' 
                    && $this->xmlReader->depth === $depth) {
                    break;
                }

                if ($this->xmlReader->nodeType === GlobalXMLReader::ELEMENT && $this->xmlReader->name === 'field') {
                    $name = $this->xmlReader->getAttribute('label');
                    if ($name === null || $name === '') {
                        continue;
                    }

                    $value = $this->readFieldValue();
                    $row[$name] = $value;
                }
            }
            return $row;
        } catch (Exception $e)  {
            throw  $e;
        }
    }

    /**
     * read field value
     *
     * @return string
     */
    private function readFieldValue(): string
    {

     try {
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
            $this->xmlReader->close();
        } catch (Exception $e)  {
            throw  $e;
        }
    }
}
