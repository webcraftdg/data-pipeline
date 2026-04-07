<?php
/**
 * ColumnMapper.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package  webcraftdg\dataPipeline\mappers
 */
namespace webcraftdg\dataPipeline\mappers;

use Exception;
use webcraftdg\dataPipeline\configs\ColumnMapping;
use webcraftdg\dataPipeline\configs\PipelineConfig;
use webcraftdg\dataPipeline\interfaces\DataMapperInterface;
use webcraftdg\dataPipeline\interfaces\TransformerInterface;
use webcraftdg\dataPipeline\registry\TransformerRegistry;

class ColumnMapper implements DataMapperInterface 
{


    public function __construct(
        private TransformerRegistry $transformerRegistry
    )
    {
    }
    /**
     * map
     *
     * @param  array        $rawRecord
     * @param  PipelineConfig $config
     *
     * @return array
     */
    public function map(array $rawRecord, PipelineConfig $config): array
    {
        try {
            $attributes = [];
            foreach ($rawRecord  as $field => $value) {
                /**@var ColumnMapping $column */
                $column = $config->findColumnByName($field);
                if (
                    $value !== null
                    && $column !== null
                    && empty($column->transformers) === false
                ) {
                    /**@var TransformerInterface $transformerConfig */
                    foreach($column->transformers as $transformerConfig) {
                        $value = $this->transformerRegistry->apply(
                            $transformerConfig->name,
                            $value,
                            $transformerConfig->options
                        );
                    }
                   
                }
                if ($column !== null) {
                    $attributes[$column->outputKey]  = $value;
                } else {
                    $attributes[$field]  = $value;
                }
            }
            return $attributes;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}