<?php
/**
 * SourceConfig.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\configs
 */
namespace webcraftdg\dataPipeline\configs;

abstract class DataConfig 
{
    public function __construct(
        public string $type,
        array $options = []
    )
    {
    }
}