<?php
/**
 * BooleanTransformer.php
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

class BooleanColumnTransformer implements TransformerInterface, ValidateRulesInterface
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'boolean';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Convertit un bool en libellé';
    }

    /**
     * @return array[]
     */
    public function getOptionsSchema(): array
    {
        return [
            ['key' => 'true', 'type'=>'string','required'=>true,'label'=>'Valeur si vrai'],
            ['key' => 'false', 'type'=>'string','required'=>true,'label'=>'Valeur si faux'],
        ];
    }

    /**
     * rules
     *
     * @return array
     */
    public static function rules() : array
    {
        return [
            'true' => ['required' => true, 'type' => 'string'],
            'false' => ['required' => true, 'type' => 'string'],
        ];
    }

    /**
     * @param mixed $value
     * @param array $options
     * @return mixed
     */
    public function transform(mixed $value, array $options = []): mixed
    {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN)
            ? $options['true']
            : $options['false'];
    }
}
