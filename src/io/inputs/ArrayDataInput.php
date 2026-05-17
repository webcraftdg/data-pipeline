<?php
/**
 * ArrayDataInput.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\io\inputs
 */
namespace webcraftdg\dataPipeline\io\inputs;

use webcraftdg\dataPipeline\interfaces\InputCountableInterface;
use webcraftdg\dataPipeline\interfaces\ValidateRulesInterface;
use webcraftdg\dataPipeline\configs\SourceConfig;
use webcraftdg\dataPipeline\interfaces\RuntimeContextInterface;
use webcraftdg\dataPipeline\rules\FileRules;
use yii\helpers\ArrayHelper;
use InvalidArgumentException;

class ArrayDataInput implements InputCountableInterface, ValidateRulesInterface
{

    private array $rows;
    private $options;
    private int $batchSize = 200;
    private ?RuntimeContextInterface $context;

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
        $this->rows = ($this->options['rows']) ?? null;
        if ($this->rows === null) {
            throw new InvalidArgumentException('ArrayExportData excepted params "rows"');
        }
        $this->batchSize = ($this->options['batchSize']) ?? $this->batchSize;
        $this->context = $context;
    }

    /**
     * open
     *
     * @param  array $options
     *
     * @return void
     */
    public function open(): void
    {
    }

    /**
     * rules
     *
     * @return array
     */
    public static function rules() : array
    {
        $local =  [
            'rows' => [
                'name' => 'rows',
                'label' => 'Tableau source key => value',
                'type' => 'array',
                'input' => false,
                'runtimeRequired' => true,
                'required' => true,
            ],
        ];
        $batchSize = FileRules::rulesBatchSize();
        return ArrayHelper::merge($local, $batchSize);
    }
    /**
     * @return iterable
     */
    public function read(): iterable
    {
        $indexBatch = 0;
        $batch = [];
        foreach ($this->rows as $row) {
            $batch[] = $row;
            $indexBatch ++;
            if ($indexBatch >= $this->batchSize) {
                yield $batch;
                $batch = [];
                $indexBatch = 0;
            }
        }

        if (empty($batch) === false) {
            yield $batch;
        }
    }

    /**
     * @return int
     * @throws \yii\db\Exception
     */
    public function count() : int
    {
        return count($this->rows);
    }

    /**
     * close
     *
     * @return void
     */
    public function close(): void
    {
        $this->rows = [];
    }
}
