<?php
/**
 * PipelineRuntimeFactory.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\runtimes
 */
namespace webcraftdg\dataPipeline\runtimes;

use webcraftdg\dataPipeline\configs\PipelineConfig;
use webcraftdg\dataPipeline\mappers\ColumnMapper;
use webcraftdg\dataPipeline\registry\InputRegistry;
use webcraftdg\dataPipeline\registry\OutputRegistry;
use webcraftdg\dataPipeline\registry\ProcessorRegistry;
use webcraftdg\dataPipeline\registry\TransformerRegistry;

final class PipelineRuntimeFactory
{

  
    /**
     * constructor
     *
     * @param  \webcraftdg\dataPipeline\registry\InputRegistry       $inputRegistry
     * @param  \webcraftdg\dataPipeline\registry\OutputRegistry      $outputRegistry
     * @param  \webcraftdg\dataPipeline\registry\ProcessorRegistry   $processorRegistry
     * @param  \webcraftdg\dataPipeline\registry\TransformerRegistry $transformerRegistry
     */
    public function __construct(
        public InputRegistry $inputRegistry,
        public OutputRegistry $outputRegistry,
        public ProcessorRegistry $processorRegistry,
        public TransformerRegistry $transformerRegistry
    )
    {
    }


    /**
     * create
     *
     * @param  \webcraftdg\dataPipeline\configs\PipelineConfig $config
     *
     * @return PipelineRuntime
     */
    public function create(PipelineConfig $config): PipelineRuntime
    {
        $input = $this->inputRegistry->create($config);
        $output = $this->outputRegistry->create($config);
        $processor = $config->processor !== null
            ? $this->processorRegistry->create($config)
            : null;

        $columnMapper = new ColumnMapper($this->transformerRegistry);

        return new PipelineRuntime(
            input: $input,
            output: $output,
            processor: $processor,
            columnMapper: $columnMapper
        );
    }
}
