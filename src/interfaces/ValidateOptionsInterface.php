<?php
/**
 * ValidateRulesInterface.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\interfaces
 */
namespace webcraftdg\dataPipeline\interfaces;

use webcraftdg\dataPipeline\exceptions\ErrorCollector;

interface ValidateOptionsInterface
{

    /**
     * rules 
     * 
     * @return void
     */
    public function validateOptions(array $options, ErrorCollector $errorCollector): void;
}