<?php
/**
 * PipelineConfig.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\configs
 */
namespace webcraftdg\dataPipeline\configs;

use webcraftdg\dataPipeline\supports\enums\PipelineMaxLimit;

class LimiterConfig 
{

    public function __construct(
        public int $rows,
        public int $columns,
        public int $estimatedMb,
        public string $format,
        public string $name,
        public int $limit,
        public int $offset,
        array $options = []
    )
    { 
    }

      /**
     * @param LimiterModel $limiterModel
     * @return string|null
     * @throws Exception
     */
    public function assertAllowed(): string | null
    {
        try {
            $rows    = $this->rows ?? 0;
            $columns = $this->columns ?? 0;
            $mb      = $this->estimatedMb ?? 0;
            $statementName = $this->name;

            $message = null;
            if ($rows > PipelineMaxLimit::ROWS) {
                $message = $this->deny('Export trop volumineux : '.$rows.' lignes', $statementName);
            }
            if ($columns > PipelineMaxLimit::COLUMNS) {
                $message = $this->deny('Export trop large : '.$columns.' colonnes', $statementName);
            }
            if ($mb > PipelineMaxLimit::ESTIMATED_MB) {
                $message = $this->deny('Export estimé à {'.$mb.'} MB, trop lourd pour le navigateur', $statementName);
            }
            return $message;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * @param string $reason
     * @param string $statementName
     * @return string
     */
    protected function deny(string $reason, string $statementName): string
    {
        return
             'Export impossible via l’interface web<br/>'
            .$reason.'<br/>'
            .'Merci d\'utiliser la commande CLI :<br/>'
            .'php yii.php fractalCmsImportExport:import-export/index  -name='.$statementName.' -version={versionActive}';
    }
}