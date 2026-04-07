<?php
/**
 * ReaderContext.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\runtime\contexts
 */
namespace fractalCms\importExport\runtime\contexts;

use webcraftdg\dataPipeline\contexts\ExecutionContext;
use webcraftdg\dataPipeline\exceptions\ErrorCollector;
use webcraftdg\dataPipeline\configs\PipelineConfig;

final class ReaderContext extends ExecutionContext
{

    /**
     * Undocumented function
     *
     * @param  \webcraftdg\dataPipeline\configs\PipelineConfig    $config
     * @param  \webcraftdg\dataPipeline\exceptions\ErrorCollector $errors
     * @param  bool                                               $dryRun
     * @param  int                                                $rowNumber
     * @param  array                                              $params
     */
    public function __construct(
        public PipelineConfig $config,
        public ErrorCollector $errors,
        public bool $dryRun,
        public int $rowNumber,
        public array $params = []
    ) {
        parent::__construct(
            config: $config,
            dryRun: $dryRun,
            hasPreamble:false,
            rowNumber: $rowNumber,
            params: $params
        );

        $this->errors = $errors;
    }
}
