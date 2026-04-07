<?php
/**
 * BooleanTransformer.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\registry
 */
namespace webcraftdg\dataPipeline\registry;

use webcraftdg\dataPipeline\interfaces\RegistryInterface;
use Exception;
use webcraftdg\dataPipeline\configs\PipelineConfig;
use webcraftdg\dataPipeline\interfaces\DataReaderInterface;

class ReaderRegistry implements RegistryInterface
{


    /**
     * create
     *
     * @param  PipelineConfig      $config
     *
     * @return DataReaderInterface
     */
    public function create(PipelineConfig $config): DataReaderInterface
    {
        try {
            $config->source->type
        } catch (Exception $e)  {
            throw  $e;
        }
    }
}
