<?php
/**
 * RuntimeContextInterface.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\interfaces
 */
namespace webcraftdg\dataPipeline\interfaces;

interface RuntimeContextInterface
{
    /**
     * get
     *
     * @param  string $key
     *
     * @return mixed
     */
    public function get(string $key): mixed;

    /**
     * has
     *
     * @param  string $key
     *
     * @return bool
     */
    public function has(string $key): bool;
}
