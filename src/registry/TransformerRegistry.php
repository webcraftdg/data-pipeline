<?php
/**
 * TransformerRegistry.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\registry
 */
namespace webcraftdg\dataPipeline\registry;

use webcraftdg\dataPipeline\interfaces\TransformerInterface;

class TransformerRegistry
{
    /** @var array <string, string> */
    private array $map = [];

    /**
     * @param array $transformers
     */
    public function __construct(array $transformers)
    {
        foreach ($transformers as $name => $transformerClass) {
            $this->map[$name] = $transformerClass;
        }
    }

    /**
     * register
     *
     * @param  string $name
     * @param  string $transformerClass
     *
     * @return void
     */
    public function register(string $name, string $transformerClass) : void
    {
        if (isset($this->map[$name]) === false) {
            $this->map[$name] = $transformerClass;
        }
    }

    /**
     * @param string $name
     * @param mixed $value
     * @param array $options
     * @return mixed
     */
    public function apply(string $name, mixed $value, mixed $options = []): mixed
    {
        $newValue = $value;
        if (empty($name) === false && isset($this->map[$name]) === true) {
            $class = $this->map[$name];
            $transformer = new $class();
            $newValue = $transformer->transform($value, $options);
        }
        return $newValue;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getTransformers(): array
    {
        return $this->map;
    }

    /**
     * has
     *
     * @param  string $name
     *
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->map[$name]);
    }

    /**
     * get class
     *
     * @param  string $name
     *
     * @return string | null
     */
    public function getClass(string $name) : string | null
    {
        return ($this->map[$name]) ?? null;
    }
}
