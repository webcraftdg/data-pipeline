<?php
/**
 * ColumnMapping.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\configs
 */
namespace webcraftdg\dataPipeline\configs;

final class ColumnMapping 
{

    /**
     * constructor
     *
     * @param  string $inputKey
     * @param  string $outputKey
     * @param  string $format
     * @param  array  $transformers
     * @param  array  $options
     */
    public function __construct(
        public string $inputKey,
        public string $outputKey,
        public string $format,
        public array  $transformers = [],
        public array $options = []
    )
    {
    }
}