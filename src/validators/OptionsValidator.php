<?php
/**
 * OptionsValidator.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\validators
 */
namespace webcraftdg\dataPipeline\validators;

use webcraftdg\dataPipeline\exceptions\ErrorCollector;
use webcraftdg\dataPipeline\exceptions\ValidationError;

final class OptionsValidator
{
    

    /**
     * validate
     *
     * @param  string                                             $path
     * @param  array                                              $rules
     * @param  array                                              $options
     * @param  \webcraftdg\dataPipeline\exceptions\ErrorCollector $errorCollector
     *
     * @return void
     */
    public function validate(string $path, array $rules, array $options, ErrorCollector $errorCollector): void
    {
        foreach($rules as $name => $rule) {
            $type = $rule['type'];
            $required = $rule['required'];
            $hasOption = in_array($name, array_keys($options));
            if ($hasOption === false && $required === true) {
                $errorCollector->add(new ValidationError(
                    path: $path.' : '.$name, 
                    message: 'This option is required',
                    level: ValidationError::LEVEL_VALIDATION_ERROR
                    )
                );
            } elseif($hasOption === true) {
                switch($type) {
                    case 'array':
                        if (is_array($options[$name]) === false) {
                            $errorCollector->add(new ValidationError(
                                path: $path.' : '.$name,
                                message: 'This option could an array',
                                level: ValidationError::LEVEL_VALIDATION_ERROR
                                )
                            );
                        }
                        break;
                    case 'string':
                        if (is_string($options[$name]) === false) {
                            $errorCollector->add(new ValidationError(
                                path: $path.' : '.$name,
                                message: 'This option could an string',
                                level: ValidationError::LEVEL_VALIDATION_ERROR
                                )
                            );
                        }
                    break;
                    case 'integer':
                    case 'int':
                        if (is_int($options[$name]) === false) {
                            $errorCollector->add(new ValidationError(
                                path: $path.' : '.$name,
                                message: 'This option could an integer',
                                level: ValidationError::LEVEL_VALIDATION_ERROR
                                )
                            );
                        }
                    break;
                    case 'boolean':
                    case 'bool':
                        if (is_bool($options[$name]) === false) {
                            $errorCollector->add(new ValidationError(
                                path: $path.' : '.$name,
                                message: 'This option could an boolean',
                                level: ValidationError::LEVEL_VALIDATION_ERROR
                                )
                            );
                        }
                        break;
                    default:
                        break;
                }
            }
        }
    }
}
