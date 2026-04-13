<?php
/**
 * PipelineMaxLimit.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\supports\enums
 */
namespace webcraftdg\dataPipeline\supports\enums;

enum PipelineMaxLimit
{
    const ROWS = 100000;
    const COLUMNS = 120;
    const ESTIMATED_MB = 500;
}
