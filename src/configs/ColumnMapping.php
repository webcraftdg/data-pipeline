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

use webcraftdg\dataPipeline\interfaces\OptionsConfigInterface;

final class ColumnMapping implements OptionsConfigInterface
{

    /**
     * constructor
     *
     * @param  string $inputKey
     * @param  string $outputKey
     * @param  array  $transformers
     * @param  array  $options
     */
    public function __construct(
        public string $inputKey,
        public string $outputKey,
        public array  $transformers = [],
        public array $options = []
    )
    {
    }

    /**
     * get options
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
