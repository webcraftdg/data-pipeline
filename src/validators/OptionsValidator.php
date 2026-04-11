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
     * @return void
     */
    public function validate(array $rules, array $options, ErrorCollector $errorCollector): void
    {
        foreach($rules as $name => $rule) {
            $type = $rule['type'];
            $required = $rule['required'];
            $hasOption = in_array($name, $options);
            if ($hasOption === false && $required === true) {
                $errorCollector->add(new ValidationError(
                    path: 'ArrayInput : '.$name, 
                    message: 'This option is required', 
                    level: ValidationError::LEVEL_VALIDATION_ERROR
                    )
                );
            } elseif($hasOption === true) {
                switch($type) {
                    case 'array':
                        if (is_array($options[$name]) === false) {
                            $errorCollector->add(new ValidationError(
                                path: 'ArrayInput : '.$name, 
                                message: 'This option could an array', 
                                level: ValidationError::LEVEL_VALIDATION_ERROR
                                )
                            );
                        } 
                        break;
                    case 'string':
                        if (is_string($options[$name]) === false) {
                            $errorCollector->add(new ValidationError(
                                path: 'ArrayInput : '.$name, 
                                message: 'This option could an string', 
                                level: ValidationError::LEVEL_VALIDATION_ERROR
                                )
                            );
                        } 
                    break;    
                    case 'integer':
                        if (is_int($options[$name]) === false) {
                            $errorCollector->add(new ValidationError(
                                path: 'ArrayInput : '.$name, 
                                message: 'This option could an integer', 
                                level: ValidationError::LEVEL_VALIDATION_ERROR
                                )
                            );
                        } 
                    break;
                }
            }
        }
    }

}
