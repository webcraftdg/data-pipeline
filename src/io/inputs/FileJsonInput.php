<?php
/**
 * FileJsonInput.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\io\inputs
 */
namespace webcraftdg\dataPipeline\io\inputs;

use webcraftdg\dataPipeline\configs\SourceConfig;
use webcraftdg\dataPipeline\interfaces\InputInterface;
use webcraftdg\dataPipeline\interfaces\RuntimeContextInterface;
use webcraftdg\dataPipeline\interfaces\ValidateRulesInterface;
use webcraftdg\dataPipeline\rules\FileRules;
use yii\helpers\ArrayHelper;

abstract class FileJsonInput
{

    protected int $batchSize = 250;
    protected array $options;
    protected string $path;
    protected ?RuntimeContextInterface $context;


    /**
     * constructor
     *
     * @param  SourceConfig                 $config
     * @param  RuntimeContextInterface|null $context
     */
    public function __construct(
        private SourceConfig $config,
        ?RuntimeContextInterface $context = null
    )
    {
        $this->options = $this->config->getOptions();
        $this->path = ($this->options['path']) ?? '';
        $this->batchSize = ($this->options['batchSize']) ?? $this->batchSize;
        $this->context = $context;
    }

        /**
     * rules
     *
     * @return array
     */
    public static function rules() : array
    {
        return ArrayHelper::merge(FileRules::rulesPath(), FileRules::rulesBatchSize());
    }
}
