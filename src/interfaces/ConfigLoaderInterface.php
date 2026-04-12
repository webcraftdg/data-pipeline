<?php
/**
 * ConfigLoaderInterface.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\interfaces
 */
namespace webcraftdg\dataPipeline\interfaces;

use webcraftdg\dataPipeline\configs\PipelineConfig;

interface ConfigLoaderInterface 
{
     /**
      * load
      *
      * @param  string         $filePath
      *
      * @return PipelineConfig | null
      */
    public static function load(string $filePath): PipelineConfig | null;
}