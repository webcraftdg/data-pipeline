<?php
/**
 * WriteTarget.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\io\writers
 */
namespace webcraftdg\dataPipeline\io\writers;

class WriteTarget
{
    public function __construct(
        public readonly string  $sheet,
        public readonly int     $rowNumber = 1,
        public readonly int     $colNumber = 1,
        public readonly ?string $style = null
    ) {}
}
