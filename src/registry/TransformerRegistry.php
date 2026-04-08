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

use Exception;
use webcraftdg\dataPipeline\transformers\BooleanColumnTransformer;
use webcraftdg\dataPipeline\transformers\DateColumnTransformer;
use webcraftdg\dataPipeline\transformers\DateXlsColumnTransformer;
use webcraftdg\dataPipeline\transformers\LowerColumnTransformer;
use webcraftdg\dataPipeline\transformers\NumberColumnTransformer;
use webcraftdg\dataPipeline\transformers\ReplaceColumnTransformer;
use webcraftdg\dataPipeline\transformers\StrPadColumnTransformer;
use webcraftdg\dataPipeline\transformers\TrimColumnTransformer;
use webcraftdg\dataPipeline\transformers\UpperColumnTransformer;

class TransformerRegistry
{
    /** @var TransformerInterface[] */
    private array $transformers = [];

    /**
     * @param array $transformers
     * @throws Exception
     */
    public function __construct(array $transformers)
    {
        try {
            foreach ($transformers as $transformer) {
                $this->transformers[$transformer->getName()] = $transformer;
            }
        } catch (Exception $e)  {
            throw  $e;
        }
    }

    /**
     * @param string $name
     * @param mixed $value
     * @param array $options
     * @return mixed
     * @throws Exception
     */
    public function apply(string $name, mixed $value, mixed $options = []): mixed
    {
        try {
            $newValue = $value;
            if (empty($name) === false && isset($this->transformers[$name]) === true) {
                $newValue = $this->transformers[$name]->transform($value, $options);
            }
            return $newValue;
        } catch (Exception $e)  {
            throw  $e;
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getTransformers(): array
    {
        try {
            return $this->transformers;
        } catch (Exception $e)  {
            throw  $e;
        }
    }

    /**
     * registry
     *
     * @param  TransformerInterface $transformer
     *
     * @return void
     */
    public function registry(TransformerInterface $transformer) : void
    {
        try {
            if (isset($this->transformers[$transformer->getName()]) === false) {
                $this->transformers[$transformer->getName()] = $transformer;
            }
        } catch(Exception $e) {
            throw $e;
        }
    }
}
