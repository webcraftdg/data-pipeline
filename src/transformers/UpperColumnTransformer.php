<?php
/**
 * UpperTransformer.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\transformers
 */
namespace webcraftdg\dataPipeline\transformers;

use webcraftdg\dataPipeline\interfaces\TransformerInterface;
use webcraftdg\dataPipeline\interfaces\ValidateRulesInterface;

class UpperColumnTransformer implements TransformerInterface, ValidateRulesInterface
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'upper';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Convertit en majuscules';
    }

    /**
     * rules
     *
     * @return array
     */
    public static function rules() : array
    {
        return [];
    }

    /**
     * @param mixed $value
     * @param array $options
     * @return mixed
     */
    public function transform(mixed $value, array $options = []): mixed
    {
        return is_string($value) ? mb_strtoupper($value) : $value;
    }
}
