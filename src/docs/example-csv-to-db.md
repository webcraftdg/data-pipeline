# EXEMPLE COMPLET : CSV → BASE DE DONNÉES AVEC LOGIQUE MÉTIER

## CONTEXTE

Nous avons un fichier CSV contenant des utilisateurs :

id;name;email;birthday
1;david;david@mail.com;1980-05-10
2;jean;;1978-11-12

### Objectif :

- importer les utilisateurs
- ignorer ceux sans email
- formater les dates
- enregistrer en base de données

--------------------------------------------------

1. CONFIGURATION DU PIPELINE

```php
$config = new PipelineConfig(
    name: 'import-users',
    version: 1,
    stopOnError: true,

    source: new SourceConfig(DataEndpointType::FILE, PipelineDataFormat::CSV, [
        'path' => __DIR__ . '/users.csv',
        'delimiter' => ';'
    ]),

    target: new TargetConfig(DataEndpointType::TABLE, 'sql', [
        'table' => 'users'
    ]),

    columns: [
        new ColumnMapping('id', 'id'),

        new ColumnMapping('name', 'name', [
            new TransformerConfig('upper')
        ]),

        new ColumnMapping('email', 'email'),

        new ColumnMapping('birthday', 'birthday', [
            new TransformerConfig('date', [
                'from' => 'Y-m-d',
                'to' => 'Y-m-d'
            ])
        ])
    ],
    processor: new ProcessorConfig('user-import')
);
```
--------------------------------------------------

2. PROCESSOR MÉTIER

```php
class UserImportProcessor implements ProcessorInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function getName() : string
    {
        return 'user-import';
    }

    public function process(array $row, array $options = []): ProcessorResult
    {
        // ignorer si email vide
        if (empty($row['email'])) {
            return new ProcessorResult(handled: true);
        }

        // insertion en base
        $stmt = $this->pdo->prepare("
            INSERT INTO users (id, name, email, birthday)
            VALUES (:id, :name, :email, :birthday)
        ");

        $stmt->execute([
            'id' => $row['id'],
            'name' => $row['name'],
            'email' => $row['email'],
            'birthday' => $row['birthday']
        ]);

        // on indique que la ligne est déjà traitée
        return new ProcessorResult(handled: true);
    }
}
```
--------------------------------------------------

3. INPUT SQL (OPTIONNEL SI LECTURE DB)

```php
class SqlInput implements InputInterface
{
    public function __construct(private PDO $pdo, private string $query)
    {
    }

    public function read(): iterable
    {
        $stmt = $this->pdo->query($this->query);

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            yield [$row];
        }
    }
}
```
--------------------------------------------------

4. OUTPUT SQL (SI ON NE PASSE PAS PAR LE PROCESSOR)

```php
class SqlOutput implements OutputInterface
{
    public function __construct(private PDO $pdo, private string $table)
    {
    }

    public function write(array $row): void
    {
        // insertion simple
    }
}
```
--------------------------------------------------

5. EXÉCUTION
```php
$processors = [
    'user-import' => UserImportProcessor::class,
 ];
$pipelineFactory = new PipelineRuntimeFactory(
    inputRegistry: new InputRegistry(), 
    outputRegistry: new OutputRegistry(), 
    processorRegistry: new ProcessorRegistry($processors), 
    transformerRegistry: new TransformerRegistry()
);

$optionValidation = new OptionsValidator();

$pipelineValidation = new PipelineConfigValidator(
    inputRegistry: $pipelineFactory->inputRegistry,
    outputRegistry: $pipelineFactory->outputRegistry,
    transformerRegistry: $pipelineFactory->transformerRegistry,
    processorRegistry: $pipelineFactory->processorRegistry,
    optionsValidator: $optionValidation
);

$errorCollector = $pipelineValidation->validate($config);

if ($errorCollector->hasErrors() === false) {
    $runtime = $pipelineFactory->create($config);
    $executor = new PipelineExecutor();
    $report = $executor->run($config, $runtime);
} else {
    return $errorCollector->toCsvRows();
}
```
--------------------------------------------------

6. RÉSULTAT

- david est inséré
- jean est ignoré (email vide)
- données transformées correctement

--------------------------------------------------

## CONCLUSION

### Ce scénario montre :

- lecture CSV
- mapping colonnes
- transformation données
- logique métier
- insertion base

C’est un cas réel complet d’import.

[Accueil](../../README.md)