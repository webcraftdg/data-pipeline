<?php
/**
 * FileRules.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\rules
 */
namespace webcraftdg\dataPipeline\rules;

final class FileRules
{

    private static $rulesRequiredText = [
        'type' => 'string',
        'input' => 'text',
        'required' => true,
        'runtimeRequired' => true,
    ];
    /**
     * rules Csv
     *
     * @return array
     */
    public static function rulesCsv() : array
    {
        return [
            'delimiter' => array_merge(
                [
                    'name' => 'delimiter',
                    'label' => 'Séparateur',
                    'default' => ';',
                ],
                static::$rulesRequiredText

            ),
            'enclosure' => array_merge(
                [
                    'name' => 'enclosure',
                    'label' => 'Container',
                    'default' => '"',
                ],
                static::$rulesRequiredText
            ),
            'inputEncoding' => array_merge(
                [
                    'name' => 'inputEncoding',
                    'label' => 'Encodage',
                    'options' => ['UTF-8', 'ISO-8859-1'],
                    'default' => 'UTF-8',
                ],
                static::$rulesRequiredText
            )
        ];
    }

    /**
     * rules headers
     *
     * @return array
     */
    public static function rulesHeader() : array
    {
        return [
            'hasHeader' => [
                'name' => 'hasHeader',
                'label' => 'Ligne d\'en-tête',
                'type' => 'bool',
                'input' => 'checkbox',
                'required' => false,
                'runtimeRequired' => false,
                'default' => true,
            ],
            'headers' => [
                'name' => 'headers',
                'label' => 'Ligne d\'en-tête',
                'type' => 'array',
                'input' => false,
                'required' => false,
                'runtimeRequired' => false,
            ],
        ];
    }

    /**
     * rules batch size
     *
     * @return array
     */
    public static function rulesBatchSize() : array
    {
        return [
            'batchSize' => [
                'name' => 'batchSize',
                'label' => 'Taille des lots traités',
                'type' => 'integer',
                'input' => 'number',
                'required' => false,
                'runtimeRequired' => false,
                'default' => 250,
            ],
        ];
    }


    /**
     * rules path
     *
     * @return array
     */
    public static function rulesPath() : array
    {
        return [
            'path' => [
                'name' => 'path',
                'label' => 'Path absolue du fichier source',
                'type' => 'string',
                'input' => false,
                'required' => false,
                'runtimeRequired' => true,
            ],
        ];
    }
}
