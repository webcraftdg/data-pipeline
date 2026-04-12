# PROCESSOR

Rôle : appliquer la logique métier.

## EXEMPLE

```php
class ValidateEmailProcessor implements RowProcessorInterface
{
    public function process(array $row): ProcessorResult
    {
        if (empty($row['email'])) {
            return new ProcessorResult(handled: true);
        }

        return new ProcessorResult(attributes: $row);
    }
}
```

## UTILISATION

```php
$processors = [
    'validate-email' => ValidateEmailProcessor::class
];
$registryProcessor =  new ProcessorRegistry($processors);
$processor = $this->processorRegistry->create($config);

```

## CAS D'USAGE

### IMPORT :

- validation
- création d'entités
- relations

### EXPORT :

- filtrage
- organisation des données

[Accueil](../../README.md)