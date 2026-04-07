<?php
/**
 * ErrorCollector.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\exceptions
 */
namespace webcraftdg\dataPipeline\exceptions;

final class ErrorCollector
{
    private array $errors = [];

    /**
     * @param PipelineError $error
     * @return void
     */
    public function add(PipelineError $error): void
    {
        $this->errors[] = $error;
    }

    /**
     * @return bool
     */
    public function hasErrors(): bool
    {
        return empty($this->errors) === false;
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->errors;
    }

    /**
     * @return array
     */
    public function toCsvRows(): array
    {
        return array_map(fn($e) => [
            'row'    => $e->rowNumber,
            'column' => $e->column,
            'error'  => $e->message,
        ], $this->errors);
    }
}
