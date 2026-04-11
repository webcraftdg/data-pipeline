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
    /** @var TransformerInterface[] */
    private array $map = [];

    /**
     * @param array $transformers
     */
    public function __construct(array $transformers)
    {
        foreach ($transformers as $transformer) {
            $this->map[$transformer->getName()] = $transformer;
        }
    }

    /**
     * register
     *
     * @param  TransformerInterface $transformer
     *
     * @return void
     */
    public function register(TransformerInterface $transformer) : void
    {
        if (isset($this->map[$transformer->getName()]) === false) {
            $this->map[$transformer->getName()] = $transformer;
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
            $newValue = $this->map[$name]->transform($value, $options);
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
     * get transformer
     *
     * @param  string               $name
     *
     * @return TransformerInterface
     */
    public function getTransformer(string $name): TransformerInterface
    {
        return ($this->map[$name]) ?? null;
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
}
