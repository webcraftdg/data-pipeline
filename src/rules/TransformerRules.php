<?php
/**
 * TransformerRules.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\rules
 */
namespace webcraftdg\dataPipeline\rules;

final class TransformerRules
{


   private static $rulesRequiredText = [
        'type' => 'string',
        'required' => true,
        'runtimeRequired' => true,
        'options' => null
    ];

    /**
     * rules Csv
     *
     * @return array
     */
    public static function rulesFromTo() : array
    {
        return [
            'from' => array_merge([
                'name' => 'from',
                'label'=>'Format de la valeur source',
            ], static::$rulesRequiredText),
            'to' =>  array_merge([
                  'name' => 'to',
                'label'=>'Format de la valeur cible',
            ], static::$rulesRequiredText),
        ];
    }

    /**
     * replace
     *
     * @return array
     */
    public static function rulesReplace() : array
    {
        return [
            'search' => array_merge([
                'name' => 'search',
                'label'=>'Rechercher',
            ], static::$rulesRequiredText),
            'replace' =>  array_merge([
                'name' => 'replace',
                'label'=>'Remplacer',
            ], static::$rulesRequiredText),
        ];
    }

    /**
     * boolean
     *
     * @return array
     */
    public static function rulesBoolean() : array
    {
        return [
            'true' => array_merge([
                'name' => 'true',
                'label'=>'Valeur si vrai',
            ], static::$rulesRequiredText),
            'false' =>  array_merge([
                'name' => 'false',
                'label'=>'Valeur si faux',
            ], static::$rulesRequiredText),
        ];
    }
}
