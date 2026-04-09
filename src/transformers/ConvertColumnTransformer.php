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
use Exception;
use DateTime;

class ConvertColumnTransformer implements TransformerInterface
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
     * @return array[]
     */
    public function getOptionsSchema(): array
    {
        return [
            ['key' => 'from', 'type'=>'text','required'=>true,'label'=>'Charset source'],
            ['key' => 'to', 'type'=>'text','required'=>true,'label'=>'Charset cible'],
        ];
    }

    /**
     * @param mixed $value
     * @param array $options
     * @return mixed
     * @throws Exception
     */
    public function transform(mixed $value, array $options = []): mixed
    {
        try {
            if(empty($value) === false) {
                $value = mb_convert_encoding($value, $options['to'], $options['from']);
            }
            return $value;
        } catch (Exception $e)  {
            throw  $e;
        }
    }
}
