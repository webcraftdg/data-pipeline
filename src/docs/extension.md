# EXTENSION

Le core ne gère pas la base de données.

## EXEMPLE SQL INPUT

```php
class SqlInput implements InputInterface
{
    public function read(): iterable
    {
        // récupération données DB
    }
}
```

## EXEMPLE SQL OUTPUT

```php
class SqlOutput implements OutputInterface
{
    public function write(array $row): void
    {
        // insertion DB
    }
}
```

## IMPORTANT

- la DB est une extension
- pas dans le core


[Accueil](../../README.md)