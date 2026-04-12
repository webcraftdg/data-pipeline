<?php
/**
 * ValidatorInterface.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\interfaces
 */
namespace webcraftdg\dataPipeline\interfaces;

use webcraftdg\dataPipeline\exceptions\ErrorCollector;

interface ValidatorInterface 
{
     /**
      * load
      *
      * @return ErrorCollector
      */
    public function validate() : ErrorCollector;
}