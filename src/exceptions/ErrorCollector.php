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
     * @param ValidationError $error
     * @return void
     */
    public function add(ValidationError $error): void
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
            'path'    => $e->path,
            'message' => $e->message,
            'level'  => $e->level,
        ], $this->errors);
    }
}
