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
use webcraftdg\dataPipeline\validators\FileConfigJsonValidator;
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
            try {
                $config = null;
                $json = file_get_contents($filePath);
                $data = json_decode($json);
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
        } catch(Exception $e) {
            throw $e;
        }
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
        try {
            
            $config = new PipelineConfig(
                name: $attributes['name'],
                version: $attributes['version'], 
                type: $attributes['type'],
                stopOnError: (isset($attributes['stopOnError']) === true) ? $attributes['stopOnError'] : false,
                fileFormat: $attributes['fileFormat']);

                return $config;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
