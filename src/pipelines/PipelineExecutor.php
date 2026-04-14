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
        $pipelineRuntime->input->open();
        $pipelineRuntime->output->open();
        try {
            $rowNumber = 0;
            foreach ($pipelineRuntime->input->read() as $rows) {
                foreach($rows as $row) {
                    $rowNumber++;
                    $report->rowsTotal++;
                    try {
                        $mappedRow = $pipelineRuntime->columnMapper->map($row, $config);
                        $mappedRow = $this->applyProcessor(
                            row: $mappedRow,
                            pipelineRuntime: $pipelineRuntime,
                            report: $report
                        );

                        if ($mappedRow === null) {
                            continue;
                        }
                        $pipelineRuntime->output->write($mappedRow);
                        $report->rowsSuccess++;
                    } catch (Exception $e) {
                        $report->rowsError++;
                        $report->errorCollector->add(new ValidationError(
                            path: 'row:'.$rowNumber,
                            message: $e->getMessage()
                        ));
                        if ($config->stopOnError) {
                            break 2;
                        }
                    }
                }
            }
            $report->success = ($report->errorCollector->hasErrors() === false);
        } finally {
            $pipelineRuntime->input->close();
            $pipelineRuntime->output->close();
        }
        return $report;
    }

    /**
     * Apply processor
     *
     * @param  array                                             $row
     * @param  \webcraftdg\dataPipeline\runtimes\PipelineRuntime $pipelineRuntime
     * @param  ExecutionReport                                   $report
     *
     * @return array|null
     */
    private function applyProcessor(array $row, PipelineRuntime $pipelineRuntime, ExecutionReport $report) : ?array
    {
        $mappedRow = $row;
        if ($pipelineRuntime->processor !== null) {
            $processorResult = $pipelineRuntime->processor->process($mappedRow);
            if ($processorResult->handled === true) {
                $report->rowsSuccess++;
                $mappedRow = null;
            } else {
                $mappedRow = $processorResult->attributes ?? $mappedRow;
            }
        }
        return $mappedRow;
    }
}
