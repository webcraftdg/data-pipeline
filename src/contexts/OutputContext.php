<?php
/**
 * OutputContext.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\contexts
 */
namespace webcraftdg\dataPipeline\contexts;

final class OutputContext
{

    /**
     * $writtenHeaders
     *
     * @var array
     */
    private array $writtenPreamble = [];

   
    /**
     * constructor
     *
     * @param  string|null $sectionName
     * @param  int|null    $rowOffset
     * @param  int|null    $colOffset
     * @param  array       $headers
     * @param  array       $options
     */
    public function __construct(
        public ?string $sectionName,
        public ?int $rowOffset = 1,
        public ?int $colOffset = 1,
        public array $headers = [],
        public array $options = []
    ) {
    }
}
