<?php
/**
 * JsonFileConfigReader.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\configLoaders
 */
namespace webcraftdg\dataPipeline\configLoaders;

use webcraftdg\dataPipeline\interfaces\ConfigLoaderInterface;
use webcraftdg\dataPipeline\configs\PipelineConfig;
use webcraftdg\dataPipeline\configs\ColumnMapping;
use webcraftdg\dataPipeline\configs\ProcessorConfig;
use webcraftdg\dataPipeline\configs\SourceConfig;
use webcraftdg\dataPipeline\configs\TargetConfig;
use webcraftdg\dataPipeline\configs\TransformerConfig;
use Exception;

class JsonFileConfigReader implements ConfigLoaderInterface
{
    


    /**
     * load
     *
     * @param  string $filePath
     *
     * @return PipelineConfig | null
     */
    public static function load(string $filePath): PipelineConfig | null
    {
        try {
            $config = null;
            $json = file_get_contents($filePath);
            $data = json_decode($json, true);
            $attributes = ($data['metas']) ?? [];
            $records = ($data['records']) ?? [];
            if (empty($records) === false) {
                $record = $records[0];
                $tmpColumns = ($record['fields']) ?? [];
                $config = static::prepare($attributes, $tmpColumns);
            }
        } catch(Exception $e) {
            $config = null;
        }
        return $config;
    }
    /**
     * prepare
     *
     * @param  array          $attributes
     * @param  array          $columns
     *
     * @return PipelineConfig
     */
    protected static function prepare(array $attributes, array $columns): PipelineConfig
    {
        $processor = null;
        if(isset($attributes['processor']) === true) {
            $processor = new ProcessorConfig(
                name:$attributes['processor']['name'],
                options:($attributes['processor']['options']) ?? null
            );
        }
        $config = new PipelineConfig(
            name: $attributes['name'],
            version: $attributes['version'], 
            stopOnError: (isset($attributes['stopOnError']) === true) ? $attributes['stopOnError'] : false,
            source: new SourceConfig($attributes['source']['type'], $attributes['source']['name'], $attributes['source']['options']),
            target: new TargetConfig($attributes['target']['type'], $attributes['target']['name'], $attributes['target']['options']),
            columns: static::prepareColumns($columns),
            processor: $processor
        );

        return $config;
    }


    /**
     * prepare columns
     *
     * @param  array $columns
     *
     * @return array
     */
    protected static function prepareColumns(array $columns): array
    {
        $columnsMappging = [];
        foreach($columns as $column) {
            $transformers = [];
            if (isset($column['transformer']) === true && isset($column['transformerOptions']) === true) {
                $transformer = new TransformerConfig($column['transformer']['name'], $column['transformerOptions']);
                $transformers[] = $transformer;
            }
            $columnMapping = new ColumnMapping(
                inputKey: $column['inputKey'],
                outputKey: $column['outputKey'],
                transformers: $transformers,
                options: ($column['options']) ?? []
            );
            $columnsMappging[] = $columnMapping;
        }
        return $columnsMappging;
    }
}
