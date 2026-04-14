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
        $mappedRow = [];

        /** @var ColumnMapping $column */
        foreach($config->columns as $columnMapping) {
            $value = ($rawRecord[$columnMapping->inputKey]) ?? null;
            if (
                $value !== null
                && empty($columnMapping->transformers) === false
            ) {
                /**@var TransformerInterface $transformerConfig */
                foreach($columnMapping->transformers as $transformerConfig) {
                    $value = $this->transformerRegistry->apply(
                        $transformerConfig->name,
                        $value,
                        $transformerConfig->options
                    );
                }
            }
                $mappedRow[$columnMapping->outputKey]  = $value;

        }
        return $mappedRow;
    }
}
