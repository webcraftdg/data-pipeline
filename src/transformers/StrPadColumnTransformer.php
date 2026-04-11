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
     * @return array[]
     */
    public function getOptionsSchema(): array
    {
        return [
            ['key' => 'length', 'type'=>'number','required'=>true,'label'=>'Longueur de la chaine'],
            ['key' => 'string', 'type'=>'text', 'max' => 1, 'required'=>true,'label'=>'Valeur à ajouter'],
            ['key' => 'type', 'type'=>'list', 'required'=>true, 'label'=>'Ajouter à gauche ou droite', 'options' => [
                ['value' => STR_PAD_LEFT, 'name' => 'left'],
                ['value' => STR_PAD_RIGHT, 'name' => 'right'],
                ['value' => STR_PAD_BOTH, 'name' => 'both'],
            ]],
        ];
    }

    /**
     * rules
     *
     * @return array
     */
    public function rules() : array
    {
        return [
            'length' => ['required' => true, 'type' => 'integer'],
            'string' => ['required' => true, 'type' => 'string'],
            'type' => ['required' => true, 'type' => 'integer'],
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
