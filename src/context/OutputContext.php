<?php
/**
 * OutputContext.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\configs
 */
namespace webcraftdg\dataPipeline\context;

class OutputContext
{
    public function __construct(
        public string $colOffset,
        public string $rowOffset,
        public string $sectionName,
        public array $headers = []
    )
    {
    }
}
