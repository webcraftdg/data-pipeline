<?php
/**
 * RegistryInterface.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\interfaces
 */
namespace webcraftdg\dataPipeline\interfaces;

use webcraftdg\dataPipeline\configs\PipelineConfig;

interface RegistryInterface 
{
     /**
      * create
      *
      * @param  PipelineConfig      $config
      *
      * @return DataReaderInterface
      */
    public static function create(PipelineConfig $config): DataReaderInterface;
}