<?php
/**
 * ConvertColumnTransformer.php
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

class ConvertColumnTransformer implements TransformerInterface, ValidateRulesInterface
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'convert';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Convertit une chaine d\'une charset à un autre';
    }


    /**
     * rules
     *
     * @return array
     */
    public static function rules() : array
    {
        return [
            'from' => ['required' => true, 'type' => 'string','label'=>'Charset source'],
            'to' => ['required' => true, 'type' => 'string','label'=>'Charset cible'],
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
            $value = mb_convert_encoding($value, $options['to'], $options['from']);
        }
        return $value;
    }
}
