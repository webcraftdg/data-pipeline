<?php
/**
 * NumberTransformer.php
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

class NumberColumnTransformer implements TransformerInterface, ValidateRulesInterface
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'decimals';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Force une valeur numérique';
    }

    /**
     * @return array[]
     */
    public function getOptionsSchema(): array
    {
        return [
            ['key' => 'decimals', 'type'=>'number','required'=>false,'label'=>'Décimales'],
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
            'decimals' => ['required' => false, 'type' => 'integer'],
        ];
    }
    /**
     * @param mixed $value
     * @param array $options
     * @return mixed
     */
    public function transform(mixed $value, array $options = []): mixed
    {
        if (is_numeric($value) === false) {
            $value = (float)$value;
        }
        $decimals = $options['decimals'] ?? 0;
        return $decimals !== null ? number_format((float)$value, $decimals, '.', '') : $value;
    }
}
