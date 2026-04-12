<?php
/**
 * SourceConfigValidator.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\validators
 */
namespace webcraftdg\dataPipeline\validators;

use webcraftdg\dataPipeline\configs\ColumnMapping;
use webcraftdg\dataPipeline\configs\PipelineConfig;
use webcraftdg\dataPipeline\exceptions\ErrorCollector;
use webcraftdg\dataPipeline\exceptions\ValidationError;
use webcraftdg\dataPipeline\interfaces\ValidateRulesInterface;
use webcraftdg\dataPipeline\registry\InputRegistry;
use webcraftdg\dataPipeline\registry\OutputRegistry;
use webcraftdg\dataPipeline\registry\ProcessorRegistry;
use webcraftdg\dataPipeline\registry\TransformerRegistry;

final class PipelineConfigValidator
{
    
    /**
     * constructor
     *
     * @param  \webcraftdg\dataPipeline\registry\InputRegistry       $inputRegistry
     * @param  \webcraftdg\dataPipeline\registry\OutputRegistry      $outputRegistry
     * @param  \webcraftdg\dataPipeline\registry\TransformerRegistry $transformerRegistry
     * @param  \webcraftdg\dataPipeline\registry\ProcessorRegistry   $processorRegistry
     * @param  OptionsValidator                                      $optionsValidator
     */
    public function __construct(
        private InputRegistry $inputRegistry,
        private OutputRegistry $outputRegistry,
        private TransformerRegistry $transformerRegistry,
        private ProcessorRegistry $processorRegistry,
        private OptionsValidator $optionsValidator
    )
    {
    }

    /**
     * validate
     *
     * @param  \webcraftdg\dataPipeline\configs\PipelineConfig    $config
     *
     * @return ErrorCollector
     */
    public function validate(PipelineConfig $config): ErrorCollector
    {
        $errorCollector = new ErrorCollector();
        // PipelineConfig
        if (empty($config->name) === true) {
            $errorCollector->add(new ValidationError(
                path:'name', 
                message: 'Pipeline name cannot be empty.', 
                level:ValidationError::LEVEL_VALIDATION_ERROR) 
            );
        }

        if (empty($config->version) === true || $config->version < 1) {
            $errorCollector->add(new ValidationError(
                path:'version', 
                message: 'Pipeline version must be >= 1.', 
                level:ValidationError::LEVEL_VALIDATION_ERROR) 
            );
        }

        if (empty($config->columns) === true) {
            $errorCollector->add(new ValidationError(
                path:'columns', 
                message: 'At least one column mapping is required.', 
                level:ValidationError::LEVEL_VALIDATION_ERROR) 
            );
        }

        // Source
        $errorCollector = $this->validateSourceConfig($config, $errorCollector);
        // Target
        $errorCollector = $this->validateTargetConfig($config, $errorCollector);
        //columns
        $errorCollector = $this->validateConfigColumns($config, $errorCollector);
        // Processor
        $errorCollector = $this->validateProcessorConfig($config, $errorCollector);
        return $errorCollector;
    }


    /**
     * validate config columns
     *
     * @param  \webcraftdg\dataPipeline\configs\PipelineConfig    $config
     * @param  \webcraftdg\dataPipeline\exceptions\ErrorCollector $errorCollector
     *
     * @return ErrorCollector
     */
    public function validateConfigColumns(PipelineConfig $config, ErrorCollector $errorCollector) : ErrorCollector
    {
        $outputKeys = [];
        foreach ($config->columns as $i => $column) {
            $columnPath = 'columns[' . $i . ']';

            if (empty($column->inputKey) === true) {
                $errorCollector->add(new ValidationError(
                    path: $columnPath . '.inputKey', 
                    message: 'Input key cannot be empty.', 
                    level:ValidationError::LEVEL_VALIDATION_ERROR) 
                );
            }

            if (empty($column->outputKey) === true) {
                 $errorCollector->add(new ValidationError(
                    path: $columnPath . '.outputKey', 
                    message: 'OutputKey key cannot be empty.', 
                    level:ValidationError::LEVEL_VALIDATION_ERROR) 
                );
            }

            if (empty($column->outputKey) === false) {
                if (isset($outputKeys[$column->outputKey]) === true) {
                    $errorCollector->add(new ValidationError(
                        path: $columnPath . '.outputKey', 
                        message: sprintf('Duplicate output key "%s".', $column->outputKey), 
                        level:ValidationError::LEVEL_VALIDATION_ERROR) 
                    );
                } else {
                    $outputKeys[$column->outputKey] = true;
                }
            
            }
            //transformer
            $errorCollector = $this->validateColumnTransformer($columnPath, $column, $errorCollector);
        }
        return $errorCollector;
    }

    /**
     * validate column transformer
     *
     * @param  string                                             $columnPath
     * @param  \webcraftdg\dataPipeline\configs\ColumnMapping     $column
     * @param  \webcraftdg\dataPipeline\exceptions\ErrorCollector $errorCollector
     *
     * @return \webcraftdg\dataPipeline\exceptions\ErrorCollector
     */
    public function validateColumnTransformer(string $columnPath, ColumnMapping $column, ErrorCollector $errorCollector) : ErrorCollector
    {
        foreach ($column->transformers as $j => $transformer) {
            $transformerPath = $columnPath . '.transformers[' . $transformer->name. ']';

            if (empty($transformer->name) === true) {
                $errorCollector->add(new ValidationError(
                    path: $transformerPath . '.name', 
                    message: 'Transformer name cannot be empty.', 
                    level:ValidationError::LEVEL_VALIDATION_ERROR) 
                );
                continue;
            }

            if ($this->transformerRegistry->has($transformer->name) === false) {
                $errorCollector->add(new ValidationError(
                    path: $transformerPath . '.name', 
                    message: sprintf('Unknown transformer "%s".', $transformer->name), 
                    level:ValidationError::LEVEL_VALIDATION_ERROR) 
                );
                continue;
            }

            $class = $this->transformerRegistry->getClass($transformer->name);

            if ($class !== null && method_exists($class, 'rules') === true) {
                $this->optionsValidator->validate(
                    $transformerPath,
                    $class::rules(),
                    $transformer->options,
                    $errorCollector
                );
            }
        }
        return $errorCollector;
    }

    /**
     * Validate target config
     *
     * @param  \webcraftdg\dataPipeline\configs\PipelineConfig    $config
     * @param  \webcraftdg\dataPipeline\exceptions\ErrorCollector $errorCollector
     *
     * @return \webcraftdg\dataPipeline\exceptions\ErrorCollector
     */
    public function validateTargetConfig(PipelineConfig $config, ErrorCollector $errorCollector) : ErrorCollector
    {
        if (empty($config->target) === true) {
            $errorCollector->add(new ValidationError(
                path:'target', 
                message: 'Target name cannot be empty.', 
                level:ValidationError::LEVEL_VALIDATION_ERROR) 
            );
        } elseif ($this->outputRegistry->has($config->target->name) === false) {
            $errorCollector->add(new ValidationError(
                path:'target.name', 
                message: sprintf('Unknown output "%s".', $config->target->name), 
                level:ValidationError::LEVEL_VALIDATION_ERROR) 
            );
        } else {
            $class = $this->outputRegistry->getClass($config->target->name);

            if ($class !== null && method_exists($class, 'rules') === true) {
                $this->optionsValidator->validate(
                    'TargetConfig : '.$config->target->name,
                    $class::rules(),
                    $config->target->options,
                    $errorCollector
                );
            }
        }
        return $errorCollector;
    }

    /**
     * validate source config
     *
     * @param  \webcraftdg\dataPipeline\configs\PipelineConfig    $config
     * @param  \webcraftdg\dataPipeline\exceptions\ErrorCollector $errorCollector
     *
     * @return \webcraftdg\dataPipeline\exceptions\ErrorCollector
     */
    public function validateSourceConfig(PipelineConfig $config, ErrorCollector $errorCollector) : ErrorCollector
    {
        if (empty($config->source) === true) {
            $errorCollector->add(new ValidationError(
                path:'source', 
                message: 'Source name cannot be empty.', 
                level:ValidationError::LEVEL_VALIDATION_ERROR) 
            );
        } elseif ($this->inputRegistry->has($config->source->name) === false) {
            $errorCollector->add(new ValidationError(
                path:'source.name', 
                message: sprintf('Unknown input "%s".', $config->source->name), 
                level:ValidationError::LEVEL_VALIDATION_ERROR) 
            );
        } else {
            $class = $this->inputRegistry->getClass($config->source->name);

            if ($class !== null && method_exists($class, 'rules') === true) {
                $this->optionsValidator->validate(
                    'SourceConfig : '.$config->source->name,
                    $class::rules(),
                    $config->source->options,
                    $errorCollector
                );
            }
        }
        return $errorCollector;
    }

    /**
     * validate processor
     *
     * @param  \webcraftdg\dataPipeline\configs\PipelineConfig    $config
     * @param  \webcraftdg\dataPipeline\exceptions\ErrorCollector $errorCollector
     *
     * @return \webcraftdg\dataPipeline\exceptions\ErrorCollector
     */
    public function validateProcessorConfig(PipelineConfig $config, ErrorCollector $errorCollector) : ErrorCollector
    {
        if ($config->processor !== null) {
            if (empty($config->processor->name) === true) {
                $errorCollector->add(new ValidationError(
                    path: 'processor.name', 
                    message: 'Processor name cannot be empty.', 
                    level:ValidationError::LEVEL_VALIDATION_ERROR) 
                );
            } elseif ($this->processorRegistry->has($config->processor->name) === false) {
                $errorCollector->add(new ValidationError(
                    path: 'processor.name', 
                    message: sprintf('Unknown processor "%s".', $config->processor->name), 
                    level:ValidationError::LEVEL_VALIDATION_ERROR) 
                );
            } else {
                $class = $this->processorRegistry->getClass($config->processor->name);

                if ($class !== null && method_exists($class, 'rules') === true) {
                    $this->optionsValidator->validate(
                        'ProcessorConfig : '.$config->processor->name,
                        $class::rules(),
                        $config->processor->options,
                        $errorCollector
                    );
                }
            }
        }
        return $errorCollector;
    }

}
