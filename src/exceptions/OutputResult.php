<?php
/**
 * OutputResult.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\exceptions
 */
namespace webcraftdg\dataPipeline\exceptions;

final class OutputResult
{
    public function __construct(
        public readonly bool $success,
        public readonly array $errors = []
    ) {}
}
