<?php
/**
 * PipelineConfig.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\configs
 */
namespace webcraftdg\dataPipeline\configs;

class ProcessorConfig 
{
    public function __construct(
        public string $name,
        array $options = []
    )
    { 
    }
}