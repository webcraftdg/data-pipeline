<?php
/**
 * ConfigLoaderInterface.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\interfaces
 */
namespace webcraftdg\dataPipeline\interfaces;

interface ConfigLoaderInterface 
{

    /**
     * Open
     *
     * @return void
     */    
    public function open(): bool;
    
    /**
     * Hydrate
     *
     * @return array
     */
    public function read(): array;

    /**
     * close
     *
     * @return void
     */
    public function delete(): void;
}