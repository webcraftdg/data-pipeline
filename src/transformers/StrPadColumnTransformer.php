<?php
/**
 * StrPadColumnTransformer.php
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

class StrPadColumnTransformer implements TransformerInterface, ValidateRulesInterface
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'str-pad';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Ajouter à une chaine de caractères des caractères avant ou après';
    }

    /**
     * rules
     *
     * @return array
     */
    public static function rules() : array
    {
        return [
            'length' => [
                'name' => 'length',
                'label'=>'Longueur de la chaine',
                'type' => 'integer',
                'required' => true,
                'runtimeRequired' => true,
                'options' => null
            ],
            'string' => [
                'name' => 'string',
                'label'=>'Valeur à ajouter',
                'type' => 'string',
                'required' => true,
                'runtimeRequired' => true,
                'options' => null
            ],
            'type' => [
                'name' => 'type',
                'label'=>'Ajouter à gauche ou droite',
                'type' => 'integer',
                'required' => true,
                'runtimeRequired' => true,
                'options' => [
                    ['value' => STR_PAD_LEFT, 'name' => 'left'],
                    ['value' => STR_PAD_RIGHT, 'name' => 'right'],
                    ['value' => STR_PAD_BOTH, 'name' => 'both'],
                ],
            ],
        ];
    }

    /**
     * @param mixed $value
     * @param array $options
     * @return mixed
     */
    public function transform(mixed $value, array $options = []): mixed
    {
        if(empty($value) === false) {
            $length = $options['length'] ?? 0;
            $string = $options['string'] ?? '';
            $type = $options['type'] ?? STR_PAD_RIGHT;
            $value = str_pad($value, $length, $string, $type);
        }
        return $value;
    }
}
