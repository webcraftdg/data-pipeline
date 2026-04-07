<?php
/**
 * RowProcessorResult.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\exceptions
 */
namespace webcraftdg\dataPipeline\exceptions;

final class RowProcessorResult
{
    /**
     * Contructeur
     *
     * handled = true, le traitement est fini le service passera à la ligne suivante
     * handled = false, le service doit continuer
     *
     * @param array|null $attributes
     * @param bool $handled
     */
    public function __construct(
        public readonly ?array $attributes = null,
        public readonly bool $handled = false
    ) {}
}
