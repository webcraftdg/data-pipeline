<?php
/**
 * HeadersBuider.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\builders
 */
namespace webcraftdg\dataPipeline\builders;

use webcraftdg\dataPipeline\configs\PipelineConfig;
use webcraftdg\dataPipeline\configs\ColumnMapping;
use Exception;

final class HeadersBuider
{

    /**
     * from pipeline
     *
     * @param  PipelineConfig $config
     *
     * @return array
     */
    public static function fromPipeline(PipelineConfig $config): array
    {
        return array_map(
            fn(ColumnMapping $c) => $c->inputKey,
            $config->columns
        );
    }
}
