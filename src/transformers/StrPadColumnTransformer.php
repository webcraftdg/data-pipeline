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
            'length' => ['required' => true, 'type' => 'integer','label'=>'Longueur de la chaine'],
            'string' => ['required' => true, 'type' => 'string','label'=>'Valeur à ajouter'],
            'type' => ['required' => true, 'type' => 'integer', 'label'=>'Ajouter à gauche ou droite', 'options' => [
                ['value' => STR_PAD_LEFT, 'name' => 'left'],
                ['value' => STR_PAD_RIGHT, 'name' => 'right'],
                ['value' => STR_PAD_BOTH, 'name' => 'both'],
            ]],
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
