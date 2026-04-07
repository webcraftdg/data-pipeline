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

use webcraftdg\dataPipeline\supports\enums\DataEndpointType;

class PipelineConfig 
{
    /**
     * constructor
     *
     * @param  string               $name
     * @param  int                  $version
     * @param  string               $type
     * @param  bool                 $stopOnError
     * @param  string               $fileFormat
     * @param  SourceConfig         $source
     * @param  TargetConfig         $target
     * @param  array                $columns
     * @param  ProcessorConfig|null $processor
     * @param  LimiterConfig|null   $limiter
     * @param  array                $options
     */
    public function __construct(
        public string $name,
        public int $version,
        public string $type,
        public bool $stopOnError,
        public string $fileFormat,
        public SourceConfig $source,
        public TargetConfig $target,
        public array $columns = [],
        public ?ProcessorConfig $processor = null,
        public ?LimiterConfig $limiter = null,
        public array $options = []
    )
    {
    }

    /**
     * is Import
     *
     * @return bool
     */
    public function isImport() : bool
    {
        return $this->type === DataEndpointType::IMPORT;
    }

    /**
     * is Export
     *
     * @return bool
     */
    public function isExport() : bool
    {
        return $this->type === DataEndpointType::EXPORT;
    }

    /**
     * findColumnByName
     *
     * @param  string        $name
     *
     * @return ColumnMapping
     */
    public function findColumnByName(string $name): ColumnMapping
    {
        $columnFind = null;
        foreach($this->columns as $column){
            if ($column->inputKey == $name) {
                $columnFind = $column;
                break;
            }
        }
        return $columnFind;
    }
}