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

interface ValidateRulesInterface
{

    /**
     * rules 
     * 
     * @return array
     */
    public static function rules(): array;
}