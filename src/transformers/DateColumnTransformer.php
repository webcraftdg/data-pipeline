<?php
/**
 * DateTransformer.php
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
use DateTime;

class DateColumnTransformer implements TransformerInterface, ValidateRulesInterface
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'date';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Convertit un format de date';
    }

    /**
     * @return array[]
     */
    public function getOptionsSchema(): array
    {
        return [
            ['key' => 'from', 'type'=>'text','required'=>true,'label'=>'Format source'],
            ['key' => 'to', 'type'=>'text','required'=>true,'label'=>'Format cible'],
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
            'from' => ['required' => true, 'type' => 'string'],
            'to' => ['required' => true, 'type' => 'string'],
        ];
    }

    /**
     * @param mixed $value
     * @param array $options
     * @return mixed
     */
    public function transform(mixed $value, array $options = []): mixed
    {
        $date = $value;
        if(empty($value) === false) {
            $dateTime = DateTime::createFromFormat($options['from'], (string)$value);
            if ($dateTime !== false) {
                $date = $dateTime->format($options['to']);
            }
        }
        return $date;
}
}
