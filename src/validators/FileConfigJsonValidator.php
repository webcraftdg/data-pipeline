<?php
/**
 * FileConfigJsonValidator.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\validators
 */
namespace webcraftdg\dataPipeline\validators;

use webcraftdg\dataPipeline\interfaces\ValidatorInterface;
use webcraftdg\dataPipeline\exceptions\ErrorCollector;
use webcraftdg\dataPipeline\exceptions\PipelineError;
use Exception;

class FileConfigJsonValidator implements ValidatorInterface
{
    
    public function __construct(
        private string $pathFile
        )
    {}

    /**
     * validate
     *
     * @return ErrorCollector
     */
    public function validate(): ErrorCollector
    {
        try {
            $errorCollector = new ErrorCollector();
            if (file_exists($this->pathFile) === true) {
                $json = file_get_contents($this->pathFile);
                $valid = json_validate($json);
                if ($valid === false) {
                    $errorCollector->add(new PipelineError(0, 'validate JSON', 'FORMAT JSON NOT VALID'));
                } else {
                    try {
                        $data = json_decode($json, true);
                        $attributes = ($data['metas']) ?? null;
                        $records = ($data['records']) ?? null;
                        $columns = null;
                        if (empty($records) === false) {
                            $record = $records[0];
                            $columns = ($record['fields']) ?? null;
                        }
                        if ($attributes === null || $records === null || $columns === null) {
                            $errorCollector->add(new PipelineError(0, 'Validate METAS, RECORDS, FIELDS', 'metas or records or fields are not valid'));
                        }
                        $attributesRequired = ['name', 'version', 'type', 'source', 'target', 'fileFormat'];
                        if ($attributes !== null) {
                            foreach($attributesRequired as $attributeRequired) {
                                if (in_array($attributeRequired, array_keys($attributes)) === false) {
                                    $errorCollector->add(new PipelineError(0, 'Metas attribute', $attributeRequired.' is required'));
                                }
                            }
                        }
                        if (is_array($columns) === true) {
                            $errorCollector = $this->validateColumns($columns, $errorCollector);
                        }
                    } catch(Exception $e) {
                        $errorCollector->add(new PipelineError(0, 'FILE CONFIG ERROR', $e->getMessage()));
                    }
                }
            } else {
                $errorCollector->add(new PipelineError(0, 'FiLE', 'File is required'));
            }
            return $errorCollector;
        } catch(Exception $e) {
            throw $e;
        }
    }

    /**
     * Validate columns
     *
     * @param  array          $columns
     * @param  ErrorCollector $errorCollector
     *
     * @return ErrorCollector
     */
    protected function validateColumns(array $columns, ErrorCollector $errorCollector) : ErrorCollector
    {
        try {
            $attributesRequired = ['inputKey', 'outputKey', 'format'];
            foreach($columns as $index => $column) {
                foreach($attributesRequired as $attributeRequired) {
                    if (in_array($attributeRequired, array_keys($column)) === false) {
                        $errorCollector->add(new PipelineError(0, 'Column : '.$index, $attributeRequired.' is required'));
                    }
                }
            }
            return $errorCollector;
        } catch(Exception $e) {
            throw $e;
        }
    }
}
