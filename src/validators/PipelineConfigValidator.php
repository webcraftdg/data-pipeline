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
     * @param  \webcraftdg\dataPipeline\exceptions\ErrorCollector $errorCollector
     *
     * @return void
     */
    public function validate(PipelineConfig $config, ErrorCollector $errorCollector): void
    {
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

        if (empty($config->type) === true) {
            $errorCollector->add(new ValidationError(
                path:'type', 
                message: 'Pipeline type cannot be empty.', 
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
            $class = $this->inputRegistry->create($config);

            if ($class !== null && $class instanceof ValidateRulesInterface) {
                $this->optionsValidator->validate(
                    $class->rules(),
                    $config->source->options,
                    $errorCollector
                );
            }
        }

        // Target
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
            $class = $this->outputRegistry->create($config);

            if ($class !== null && $class instanceof ValidateRulesInterface) {
                $this->optionsValidator->validate(
                    $class->rules(),
                    $config->target->options,
                    $errorCollector
                );
            }
        }

        // Columns
        $outputKeys = [];

        foreach ($config->columns as $i => $column) {
            $columnPath = 'columns[' . $i . ']';

            if (empty($column->inputKey) === true) {
                $errorCollector->add(new ValidationError(
                    path: $$columnPath . '.inputKey', 
                    message: 'Input key cannot be empty.', 
                    level:ValidationError::LEVEL_VALIDATION_ERROR) 
                );
            }

            if (empty($column->outputKey) === true) {
                 $errorCollector->add(new ValidationError(
                    path: $$columnPath . '.outputKey', 
                    message: 'OutputKey key cannot be empty.', 
                    level:ValidationError::LEVEL_VALIDATION_ERROR) 
                );
            }

            if (empty($column->outputKey) === false && isset($outputKeys[$column->outputKey])) {
                $errorCollector->add(new ValidationError(
                    path: $$columnPath . '.outputKey', 
                    message: sprintf('Duplicate output key "%s".', $column->outputKey), 
                    level:ValidationError::LEVEL_VALIDATION_ERROR) 
                );
            } else {
                $outputKeys[$column->outputKey] = true;
            }

            foreach ($column->transformers as $j => $transformer) {
                $transformerPath = $columnPath . '.transformers[' . $j . ']';

                if (empty($transformer->name) === true) {
                    $errorCollector->add(new ValidationError(
                        path: $$transformerPath . '.name', 
                        message: 'Transformer name cannot be empty.', 
                        level:ValidationError::LEVEL_VALIDATION_ERROR) 
                    );
                    continue;
                }

                if ($this->transformerRegistry->has($transformer->name) === false) {
                    $errorCollector->add(new ValidationError(
                        path: $$transformerPath . '.name', 
                        message: sprintf('Unknown transformer "%s".', $transformer->name), 
                        level:ValidationError::LEVEL_VALIDATION_ERROR) 
                    );
                    continue;
                }

                $class = $this->transformerRegistry->getTransformer($transformer->name);

                if ($class !== null && $class instanceof ValidateRulesInterface) {
                    $this->optionsValidator->validate(
                        $class->rules(),
                        $transformer->options,
                        $$errorCollector
                    );
                }
            }
        }

        // Processor
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
                $class = $this->processorRegistry->create($config);

                if ($class !== null && $class instanceof ValidateRulesInterface) {
                    $this->optionsValidator->validate(
                        $class::rules(),
                        $config->processor->options,
                        $errors,
                        'processor.options'
                    );
                }
            }
        }
    }

}
