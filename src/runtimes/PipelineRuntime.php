<?php
/**
 * PipelineRuntime.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\runtimes
 */
namespace webcraftdg\dataPipeline\runtimes;

use webcraftdg\dataPipeline\interfaces\InputInterface;
use webcraftdg\dataPipeline\interfaces\OutputInterface;
use webcraftdg\dataPipeline\interfaces\ProcessorInterface;
use webcraftdg\dataPipeline\mappers\ColumnMapper;

final class PipelineRuntime
{


    /**
     * constructor
     *
     * @param  InputInterface          $input
     * @param  OutputInterface         $output
     * @param  ColumnMapper            $columnMapper
     * @param  ProcessorInterface|null $processor
     */
    public function __construct(
        public InputInterface $input,
        public OutputInterface $output,
        public ColumnMapper $columnMapper,
        public ?ProcessorInterface $processor = null,
    ) {
    }
}
