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

use webcraftdg\dataPipeline\configs\PipelineConfig;
use webcraftdg\dataPipeline\exceptions\ErrorCollector;
use webcraftdg\dataPipeline\exceptions\ValidationError;
use webcraftdg\dataPipeline\runtimes\PipelineRuntime;
use Exception;

final class PipelineExecutor
{

    /**
     * constructor
     */
    public function __construct(
    )
    {
    }

    /**
     * run
     *
     * @param  \webcraftdg\dataPipeline\configs\PipelineConfig $config
     * @param  PipelineRuntime                                 $pipelineRuntime
     *
     * @return ExecutionReport
     */
    public function run(
        PipelineConfig $config,
        PipelineRuntime $pipelineRuntime
    ): ExecutionReport {
        $report = new ExecutionReport(new ErrorCollector());
        $input = $pipelineRuntime->input;
        $output = $pipelineRuntime->output;
        $processor = $pipelineRuntime->processor;
        $columnMapper = $pipelineRuntime->columnMapper;
        $input->open();
        $output->open();
        $rowNumber = 0;
        foreach ($input->read() as $rows) {
            foreach($rows as $row) {
                $rowNumber++;
                $report->rowsTotal++;
                try {
                    $mappedRow = $columnMapper->map($row, $config);

                    if ($processor !== null) {
                        $processorResult = $processor->process($mappedRow);
                        if ($processorResult->handled === true) {
                            $report->rowsSuccess++;
                            continue;
                        }
                        $mappedRow = $processorResult->attributes ?? $mappedRow;
                    }
                    $output->write($mappedRow);
                    $report->rowsSuccess++;
                } catch (Exception $e) {
                    $report->rowsError++;
                    $report->errorCollector->add(new ValidationError(
                        path: 'row:'.$rowNumber,
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
