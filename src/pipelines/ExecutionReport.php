<?php
/**
 * ExecutionReport.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\pipelines
 */

namespace webcraftdg\dataPipeline\pipelines;

use webcraftdg\dataPipeline\exceptions\ErrorCollector;

final class ExecutionReport
{
        /**
         * $rowsTotal
         *
         * @var int
         */
        public int $rowsTotal;
        /**
         * $rowsError
         *
         * @var int
         */
        public int $rowsError;
        /**
         * $rowsSuccess
         *
         * @var int
         */
        public int $rowsSuccess;

        public ?string $outputPath;
        public ?int $duration = 0;
        public ?bool $success = false;

        /**
         * constructor
         *
         * @param  \webcraftdg\dataPipeline\exceptions\ErrorCollector $errorCollector
         */
        public function __construct(public ErrorCollector $errorCollector)
        {

        }
}
