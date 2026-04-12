<?php
/**
 * TransformTransformerInterfaceer.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package webcraftdg\dataPipeline\interfaces
 */
namespace webcraftdg\dataPipeline\interfaces;

interface TransformerInterface
{

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getDescription(): string;

    /**
     * @param mixed $value
     * @param array $options
     * @return mixed
     */
    public function transform(mixed $value, array $options = []): mixed;
}
