<?php
/**
 * PipelineExecutor.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\pipelines
 */

namespace webcraftdg\dataPipeline\pipelines;

use webcraftdg\dataPipeline\mappers\ColumnMapper;
use webcraftdg\dataPipeline\configs\PipelineConfig;
use webcraftdg\dataPipeline\exceptions\ErrorCollector;
use webcraftdg\dataPipeline\exceptions\PipelineError;
use webcraftdg\dataPipeline\interfaces\InputInterface;
use webcraftdg\dataPipeline\interfaces\OutputInterface;
use webcraftdg\dataPipeline\interfaces\ProcessorInterface;
use Exception;

final class PipelineExecutor
{

    /**
     * constructor
     *
     * @param  \webcraftdg\dataPipeline\mappers\ColumnMapper $columnMapper
     */
    public function __construct(
        private ColumnMapper $columnMapper
    )
    {
    }

    /**
     * run
     *
     * @param  \webcraftdg\dataPipeline\configs\PipelineConfig $config
     * @param  InputInterface                                  $input
     * @param  OutputInterface                                 $output
     * @param  ProcessorInterface|null                      $processor
     *
     * @return ExecutionReport
     */
    public function run(
        PipelineConfig $config,
        InputInterface $input,
        OutputInterface $output,
        ?ProcessorInterface $processor = null
    ): ExecutionReport {
        $report = new ExecutionReport(new ErrorCollector());

        $input->open();
        $output->open();

        $rowNumber = 0;

        foreach ($input->read() as $rows) {
            foreach($rows as $row) {
                $rowNumber++;
                $report->rowsTotal++;
                try {
                    $mappedRow = $this->columnMapper->map($row, $config);

                    if ($processor !== null) {
                        
                        $processorResult = $processor->process($mappedRow);
                        if ($processorResult->handled === true) {
                            $report->rowsSuccess++;
                            continue;
                        }
                    }
                    $mappedRow = $result->attributes ?? $mappedRow;
                    $output->write($mappedRow);
                    $report->rowsSuccess++;
                } catch (Exception $e) {
                    $report->rowsError++;
                    $report->errorCollector->add(new PipelineError(
                        rowNumber: $rowNumber,
                        column: '*',
                        message: $e->getMessage()
                    ));
                    if ($config->stopOnError) {
                        break;
                    }
                }
            }
        }
        if ($report->errorCollector->hasErrors() === false) {
            $report->success = true;
        }
        $input->close();
        $output->close();
        return $report;
    }
}
