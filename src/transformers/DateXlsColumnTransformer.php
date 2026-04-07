<?php
/**
 * DateXlsTransformer.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\transformers
 */
namespace webcraftdg\dataPipeline\transformers;

use PhpOffice\PhpSpreadsheet\Shared\Date;
use webcraftdg\dataPipeline\interfaces\TransformerInterface;
use Exception;

class DateXlsColumnTransformer implements TransformerInterface
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'date-xls';
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
            ['key' => 'to', 'type'=>'text','required'=>true,'label'=>'Format cible'],
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
            $date = $value;
            $to = $options['to'] ?? 'Y-m-d';

            // Cas 1 : date Excel (numérique)
            if (is_numeric($value)) {
                $date =  Date::excelToDateTimeObject($value)->format($to);
            } elseif (is_string($value)) {
                $timestamp = strtotime($value);
                if ($timestamp !== false) {
                    $date =  date($to, $timestamp);
                }
            }
            return  $date;
        } catch (Exception $e)  {
            throw  $e;
        }
    }
}
