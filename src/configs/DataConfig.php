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
        public string $name,
        public array $options = []
    )
    {
    }

    /**
     * to array
     *
     * @return array
     */
    public function toArray() : array
    {
        return [
            'type' => $this->type,
            'name' => $this->name,
            'options' => $this->options
        ];
    }
}
