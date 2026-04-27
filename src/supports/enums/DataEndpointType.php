<?php
/**
 * DataEndpointType.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\supports\enums
 */
namespace webcraftdg\dataPipeline\supports\enums;

enum DataEndpointType
{
    const FILE = 'file';
    const ARRAY = 'array';
    const DATABASE = 'database';
    const VIEW = 'view';
}
