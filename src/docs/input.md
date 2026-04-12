# INPUT

Rôle : lire des données.

## IMPORTANT

Un input retourne des lots de lignes (batchs), pas une ligne unique.

```php
foreach ($input->read() as $batch) {
    foreach ($batch as $row) {
        // traitement
    }
}
```

## POURQUOI DES BATCHS ?

- meilleure performance
- moins de mémoire
- traitement de gros fichiers

## EXEMPLE

```php
new SourceConfig('input', 'csv', [
    'path' => 'file.csv'
])
```
[Accueil](../../README.md)