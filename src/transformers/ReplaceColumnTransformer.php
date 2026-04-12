<?php
/**
 * ReplaceTransformer.php
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

class ReplaceColumnTransformer implements TransformerInterface, ValidateRulesInterface
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'replace';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Remplace du texte';
    }

    /**
     * rules
     *
     * @return array
     */
    public static  function rules() : array
    {
        return [
            'search' => ['required' => true, 'type' => 'string','label'=>'Rechercher'],
            'replace' => ['required' => true, 'type' => 'string','label'=>'Remplacer'],
        ];
    }

    /**
     * @param mixed $value
     * @param array $options
     * @return mixed
     */
    public function transform(mixed $value, array $options = []): mixed
    {
        return is_string($value)
            ? str_replace($options['search'], $options['replace'], $value)
            : $value;
    }
}
