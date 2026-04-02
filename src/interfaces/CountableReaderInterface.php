<?php
/**
 * CountableReaderInterface.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\interfaces
 */
namespace webcraftdg\dataPipeline\interfaces;

use webcraftdg\dataPipeline\interfaces\DataReaderInterface;

interface CountableReaderInterface extends DataReaderInterface
{

    /**
     * @return int
     */
    public function count() : int;
}
