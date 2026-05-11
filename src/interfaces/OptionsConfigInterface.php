<?php
/**
 * OptionsConfigInterface.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\interfaces
 */
namespace webcraftdg\dataPipeline\interfaces;

interface OptionsConfigInterface
{

    /**
     * get options
     *
     * @return array
     */
    public function getOptions(): array;
}
