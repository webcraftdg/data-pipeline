<?php
/**
 * PipelineError.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\exceptions
 */
namespace webcraftdg\dataPipeline\exceptions;

final class PipelineError
{
    const LEVEL_ERROR = 'error';
    const LEVEL_VALIDATION_ERROR = 'validationError';

    public function __construct(
        public readonly int $rowNumber,
        public readonly string $column,
        public readonly string $message,
        public readonly string $level = self::LEVEL_ERROR
    ) {}
}
