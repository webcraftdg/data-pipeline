<?php
/**
 * ExecutionContext.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\contexts
 */
namespace webcraftdg\dataPipeline\contexts;

use Exception;
use webcraftdg\dataPipeline\configs\PipelineConfig;

class ExecutionContext
{
    /**
     * constructor
     *
     * @param  \webcraftdg\dataPipeline\configs\PipelineConfig $config
     * @param  bool                                            $dryRun
     * @param  bool                                            $hasPreamble
     * @param  int                                             $rowNumber
     * @param  array                                           $params
     */
    public function __construct(
        public PipelineConfig $config,
        public bool $dryRun,
        public bool $hasPreamble,
        public int $rowNumber,
        protected array $params = []
    ) {}

    /**
     * Retourne une nouvelle instance avec un autre numéro de ligne
     *
     * @param int $rowNumber
     * @return $this
     */
    public function withRowNumber(int $rowNumber): static
    {
        try {
            $clone = clone $this;
            $clone->rowNumber = $rowNumber;
            return $clone;
        } catch (Exception $e) {
            throw  $e;
        }
    }

    /**
     * Paramètres métier (date d’import, version, etc.)
     *
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function getParam(string $key, mixed $default = null): mixed
    {
        return $this->params[$key] ?? $default;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasParam(string $key): bool
    {
        return array_key_exists($key, $this->params);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function withParam(string $key, mixed $value): static
    {
        try {
            $clone = clone $this;
            $clone->params[$key] = $value;
            return $clone;
        } catch (Exception $e) {
            throw  $e;
        }
    }
}
