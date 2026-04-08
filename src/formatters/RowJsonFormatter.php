<?php
/**
 * Record.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\formatters
 */
namespace webcraftdg\dataPipeline\formatters;

use webcraftdg\dataPipeline\interfaces\RowJsonFormatterInterface;
use webcraftdg\dataPipeline\configs\PipelineConfig;
use Exception;

class RowJsonFormatter implements RowJsonFormatterInterface 
{

    /**
     * format
     *
     * @param  array          $rawRecord
     * @param  PipelineConfig $config
     *
     * @return array
     */
    public function format(array $rawRecord, PipelineConfig $config): array
    {
         try {
            $attributes = [];
            foreach ($rawRecord  as $field => $value) {
                $rawColumn = [];
                $column = $config->findColumnByName($field);
                if ($column === null) {
                    
                }
                if ($column !== null) {
                    $rawColumn['name']  = $column->inputKey;
                }
                $rawColumn['label'] = $field;
                $rawColumn['value']  = $value;
                $attributes[$field] = $rawColumn;
            }
            return $attributes;
        } catch (Exception $e)  {
            throw  $e;
        }
    }
}