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
use webcraftdg\dataPipeline\interfaces\OptionsConfigInterface;

final class OptionsValidator
{
    

    /**
     * validate
     *
     * @param  string                                             $path
     * @param  array                                              $rules
     * @param  OptionsConfigInterface                             $dataConfig
     * @param  \webcraftdg\dataPipeline\exceptions\ErrorCollector $errorCollector
     *
     * @return void
     */
    public function validate(string $path, array $rules, OptionsConfigInterface $dataConfig, ErrorCollector $errorCollector): void
    {
        $options = $dataConfig->getOptions();
        foreach($rules as $name => $rule) {
            $type = $rule['type'];
            $required = $rule['required'];
            $when = ($rule['when']) ?? null;
            $ruleOptions = ($rule['options']) ?? [];
            $hasOption = in_array($name, array_keys($options));
            if (is_array($when) === true) {
                $this->validateWhenOption($name, $path, $when, $hasOption, $dataConfig, $errorCollector);
            } elseif ($hasOption === false && $required === true) {
                $errorCollector->add(new ValidationError(
                    path: $path.' : '.$name,
                    message: 'This option is required',
                    level: ValidationError::LEVEL_VALIDATION_ERROR
                    )
                );
            } elseif($hasOption === true) {
                switch($type) {
                    case 'array':
                        $this->validateArray($name, $options, $path, $errorCollector);
                        break;
                    case 'string':
                        $this->validateString($name, $options, $path, $errorCollector);
                        break;
                    case 'integer':
                    case 'int':
                        $this->validateInteger($name, $options, $path, $errorCollector);
                        break;
                    case 'boolean':
                    case 'bool':
                        $this->validateBoolean($name, $options, $path, $errorCollector);
                        break;
                    case 'enum':
                        $this->validateEnum($name, $options, $ruleOptions, $path, $errorCollector);
                        break;
                    default:
                        break;
                }
            }
        }
    }

    /**
     * Undocumented function
     *
     * @param  string                                             $name
     * @param  string                                             $path
     * @param  array                                              $when
     * @param  bool                                               $hasOption
     * @param  OptionsConfigInterface                             $dataConfig
     * @param  \webcraftdg\dataPipeline\exceptions\ErrorCollector $errorCollector
     *
     * @return void
     */
    private function validateWhenOption(string $name, string $path, array $when, bool $hasOption, OptionsConfigInterface $dataConfig, ErrorCollector $errorCollector) : void
    {
        foreach($when as $property => $expectedValue) {
            if (property_exists($dataConfig, $property) === true && empty($dataConfig->$property) === false && $dataConfig->$property === $expectedValue && $hasOption === false) {
                $errorCollector->add(new ValidationError(
                        path: $path.' : '.$name.' : WHEN : '.$property,
                        message: 'This option is required',
                        level: ValidationError::LEVEL_VALIDATION_ERROR
                    )
                );
            }
        }
    }

    /**
     * validate array
     *
     * @param  string                                             $name
     * @param  array                                              $options
     * @param  string                                             $path
     * @param  \webcraftdg\dataPipeline\exceptions\ErrorCollector $errorCollector
     *
     * @return void
     */
    private function validateArray(string $name, array $options, string $path, ErrorCollector $errorCollector) : void
    {
        if (is_array($options[$name]) === false) {
            $errorCollector->add(new ValidationError(
                path: $path.' : '.$name,
                message: 'This option could an array',
                level: ValidationError::LEVEL_VALIDATION_ERROR
                )
            );
        }
    }

    /**
     * Validate string
     *
     * @param  string                                             $name
     * @param  array                                              $options
     * @param  string                                             $path
     * @param  \webcraftdg\dataPipeline\exceptions\ErrorCollector $errorCollector
     *
     * @return void
     */
    private function validateString(string $name, array $options, string $path, ErrorCollector $errorCollector) : void
    {
        if (is_string($options[$name]) === false) {
            $errorCollector->add(new ValidationError(
                path: $path.' : '.$name,
                message: 'This option could an string',
                level: ValidationError::LEVEL_VALIDATION_ERROR
                )
            );
        }
    }

    /**
     * Validate integer
     *
     * @param  string                                             $name
     * @param  array                                              $options
     * @param  string                                             $path
     * @param  \webcraftdg\dataPipeline\exceptions\ErrorCollector $errorCollector
     *
     * @return void
     */
    private function validateInteger(string $name, array $options, string $path, ErrorCollector $errorCollector) : void
    {
        if (is_int($options[$name]) === false) {
            $errorCollector->add(new ValidationError(
                path: $path.' : '.$name,
                message: 'This option could an integer',
                level: ValidationError::LEVEL_VALIDATION_ERROR
                )
            );
        }
    }


    /**
     * Validate boolean
     *
     * @param  string                                             $name
     * @param  array                                              $options
     * @param  string                                             $path
     * @param  \webcraftdg\dataPipeline\exceptions\ErrorCollector $errorCollector
     *
     * @return void
     */
    private function validateBoolean(string $name, array $options, string $path, ErrorCollector $errorCollector) : void
    {
        if (is_bool($options[$name]) === false) {
            $errorCollector->add(new ValidationError(
                path: $path.' : '.$name,
                message: 'This option could an boolean',
                level: ValidationError::LEVEL_VALIDATION_ERROR
                )
            );
        }
    }

    /**
     * validate enum
     *
     * @param  string                                             $name
     * @param  array                                              $options
     * @param  array                                              $ruleOptions
     * @param  string                                             $path
     * @param  \webcraftdg\dataPipeline\exceptions\ErrorCollector $errorCollector
     *
     * @return void
     */
    private function validateEnum(string $name, array $options, array $ruleOptions, string $path, ErrorCollector $errorCollector) : void
    {
        if (in_array($options[$name], $ruleOptions) === false ) {
            $errorCollector->add(new ValidationError(
                path: $path.' : '.$name,
                message: 'Excepted value for this option : '.implode(',', $ruleOptions),
                level: ValidationError::LEVEL_VALIDATION_ERROR
                )
            );
        }
    }
}
