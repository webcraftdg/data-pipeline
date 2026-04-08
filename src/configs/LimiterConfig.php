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
use Exception;

class LimiterConfig 
{

    public function __construct(
        public string $name,
        public ?int $maxLimit = 100000,
        public ?int $maxOffset = 1000,
        public ?int $maxMb = 500,
        private ?array $options = []
    )
    { 
    }

    public function assertAllowed(int $rows, int $columns, int $mb): string | null
    {
        try {

            $message = null;
            if ($rows > $this->maxLimit) {
                $message = $this->deny('Export trop volumineux : '.$rows.' lignes', $this->name);
            }
            if ($columns > $this->maxOffset) {
                $message = $this->deny('Export trop large : '.$columns.' colonnes', $this->name);
            }
            if ($mb > $this->maxMb) {
                $message = $this->deny('Export estimé à {'.$mb.'} MB, trop lourd pour le navigateur', $this->name);
            }
            return $message;
        } catch (Exception $e)  {
            throw  $e;
        }
    }

    /**
     * @param string $reason
     * @param string $statementName
     * @return string
     */
    protected function deny(string $reason): string
    {
        return
             'Export impossible via l’interface web<br/>'
            .$reason.'<br/>'
            .'Merci d\'utiliser la commande CLI :<br/>'
            .'php yii.php fractalCmsImportExport:import-export/index  -name='.$this->name.' -version={versionActive}';
    }
}