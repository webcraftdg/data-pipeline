# webcraftdg/data-pipeline

Moteur générique de transformation de données en PHP.

## OBJECTIF

Ce composant permet de transformer des données d'un format à un autre de manière propre, réutilisable et maintenable.

### Exemples :

- CSV → Base de données
- Base de données → Excel
- JSON → XML
- Array → JSON

## CONCEPT

Le système repose sur un flux :

Source → Pipeline → Target

Il n'y a pas de notion technique d'import/export, seulement un flux de données.

## FONCTIONNEMENT

### Le pipeline suit ces étapes :

Input → Mapping → Transformers → Processor → Output

- Input : lit les données
- Mapping : renomme les colonnes
- Transformers : transforme les valeurs
- Processor : applique la logique métier
- Output : écrit les données

### EXEMPLE COMPLET

```php
$config = new PipelineConfig(
    name: 'users',
    version: 1,
    stopOnError: true,
    source: new SourceConfig('input', 'array', [
        'data' => [
            ['id' => 1, 'name' => 'david', 'email' => 'test@mail.com'],
            ['id' => 2, 'name' => 'jean', 'email' => '']
        ]
    ]),
    target: new TargetConfig('output', 'json', [
        'path' => 'output.json'
    ]),
    columns: [
        new ColumnMapping('id', 'ID'),
        new ColumnMapping('name', 'Name', [
            new TransformerConfig('upper')
        ])
    ],
    processor: new ProcessorConfig('validate-email')
);

$runtime = $runtimeFactory->create($config);
$report = $executor->run($config, $runtime);
```
- [Installation](./src/docs/installation.md)
- [Pipeline](./src/docs/pipeline.md)
- [Input](./src/docs/input.md)
- [Processor](./src/docs/processor.md)
- [transformer](./src/docs/transformer.md)
- [Extension](./src/docs/extension.md)
- [Exemple complet](./src/docs/example-csv-to-db.md)