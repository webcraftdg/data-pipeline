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

use webcraftdg\dataPipeline\supports\enums\PipelineConfigType;

class PipelineConfig 
{
    /**
     * constructor
     *
     * @param  string               $name
     * @param  int                  $version
     * @param  string               $type
     * @param  bool                 $stopOnError
     * @param  SourceConfig         $source
     * @param  TargetConfig         $target
     * @param  array                $columns
     * @param  ProcessorConfig|null $processor
     * @param  array                $options
     */
    public function __construct(
        public string $name,
        public int $version,
        public string $type,
        public bool $stopOnError,
        public SourceConfig $source,
        public TargetConfig $target,
        public array $columns = [],
        public ?ProcessorConfig $processor = null,
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
        return $this->type === PipelineConfigType::IMPORT;
    }

    /**
     * is Export
     *
     * @return bool
     */
    public function isExport() : bool
    {
        return $this->type === PipelineConfigType::EXPORT;
    }

    /**
     * findColumnByName
     *
     * @param  string        $name
     *
     * @return ColumnMapping | null
     */
    public function findColumnByName(string $name): ColumnMapping | null
    {
        $columnFind = null;
        foreach($this->columns as $column){
            if ($column->inputKey == $name || $column->outputKey == $name) {
                $columnFind = $column;
                break;
            }
        }
        return $columnFind;
    }
}