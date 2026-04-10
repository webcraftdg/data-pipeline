<?php
/** 
 * ValidateEmailProcessor.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\processors
 */
namespace webcraftdg\dataPipeline\processors;

use webcraftdg\dataPipeline\interfaces\ProcessorInterface;
use webcraftdg\dataPipeline\exceptions\ProcessorResult;
use RuntimeException;

final class ValidateEmailProcessor implements ProcessorInterface
{
    /**
     * get name
     *
     * @return string
     */
    public function getName(): string
    {
        return 'validate-email';
    }

    /**
     * process
     *
     * @param  array           $row
     * @param  array           $options
     *
     * @return ProcessorResult
     */
    public function process(array $row, array $options = []): ProcessorResult
    {
        if (empty($row['Email']) === true || filter_var($row['Email'], FILTER_VALIDATE_EMAIL) === false) {
            throw new RuntimeException('Invalid email.');
        }
        return new ProcessorResult($row);
    }

}