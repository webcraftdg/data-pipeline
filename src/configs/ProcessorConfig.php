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

use webcraftdg\dataPipeline\interfaces\OptionsConfigInterface;

class ProcessorConfig implements OptionsConfigInterface
{
    public function __construct(
        public string $name,
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
