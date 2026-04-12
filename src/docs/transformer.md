# TRANSFORMER

Rôle : transformer une valeur.

## EXEMPLE SIMPLE

```php
new ColumnMapping('name', 'Name', [
    new TransformerConfig('upper')
])
```

## EXEMPLE AVEC OPTIONS

```php
new ColumnMapping('birthday', 'Birthday', [
    new TransformerConfig('date', [
        'from' => 'Y-m-d',
        'to' => 'd/m/Y'
    ])
])
```

# IMPORTANT

- pas de logique métier
- uniquement transformation

[Accueil](../../README.md)