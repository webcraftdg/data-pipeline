<?php
/**
 * TrimTransformer.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\transformers
 */
namespace webcraftdg\dataPipeline\transformers;

use webcraftdg\dataPipeline\interfaces\TransformerInterface;
use Exception;

class TrimColumnTransformer implements TransformerInterface
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'trim';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Supprime les espaces avant/après';
    }

    /**
     * @return array[]
     */
    public function getOptionsSchema(): array
    {
        return [];
    }

    /**
     * @param mixed $value
     * @param array $options
     * @return mixed
     * @throws Exception
     */
    public function transform(mixed $value, array $options = []): mixed
    {
        try {
            return is_string($value) ? trim($value) : $value;
        } catch (Exception $e)  {
            throw  $e;
        }
    }
}
