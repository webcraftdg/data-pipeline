CHANGELOG
=========

Release v1.1.1 27/04/2026 <davidg@webcraftdg.fr>
----------------------------------------------

* Update Date End Point Type Enum

Release v1.1.0 13/04/2026 <davidg@webcraftdg.fr>
----------------------------------------------

* Ajout d'exception
* Reformat code
* Update tests

Release v1.0.0 12/04/2026 <davidg@webcraftdg.fr>
----------------------------------------------

### 🎉 Ajout

- Première version stable du composant `data-pipeline`
- Implémentation d’un moteur générique de transformation de données
- Support des flux de données : Source → Pipeline → Target
- Mise en place des composants principaux :
  - `PipelineConfig`
  - `SourceConfig`
  - `TargetConfig`
  - `ColumnMapping`
  - `TransformerConfig`
  - `ProcessorConfig`
- Implémentation des registries :
  - `InputRegistry`
  - `OutputRegistry`
  - `TransformerRegistry`
  - `ProcessorRegistry`
- Ajout du système de runtime :
  - `PipelineRuntime`
  - `PipelineRuntimeFactory`
- Implémentation du moteur d’exécution :
  - `PipelineExecutor`
- Support des batchs dans les Inputs (lecture par lot)
- Ajout des transformers de base :
  - upper, lower, trim, number, date, replace, boolean, str-pad
- Ajout du système de processor (logique métier)
- Ajout du système de validation :
  - `PipelineConfigValidator`
  - `OptionsValidator`
  - `ErrorCollector`
- Support des formats :
  - CSV
  - JSON / NDJSON
  - XML
  - XLSX
  - Array

---

### 🔄 Modifié

- Suppression du concept de `type` dans `PipelineConfig`
  - Le flux est désormais entièrement défini par `SourceConfig` et `TargetConfig`
- Clarification de l’architecture :
  - séparation entre configuration, runtime et exécution
- Refactorisation du `PipelineExecutor`
  - suppression de la résolution interne des dépendances
  - utilisation d’un `PipelineRuntime`
- Amélioration de la lisibilité du système de mapping
- Harmonisation des transformers via `TransformerRegistry`
- Mise en place d’un modèle extensible pour les inputs et outputs

---

### 🧠 Conceptuel

- Abandon du modèle "import/export" au profit d’un modèle de **flux de données**
- Introduction d’un pipeline **agnostique du métier et du framework**
- Séparation stricte :
  - transformation de données (core)
  - logique métier (processor)
  - persistance (extensions)

---

### ⚠️ Important

- Le core ne gère pas :
  - la base de données
  - SQL
  - les frameworks (Yii2, Symfony, etc.)
- Ces fonctionnalités doivent être implémentées via des extensions :
  - exemple : `SqlInput`, `SqlOutput`

---

### 🧪 Tests

- Mise en place de tests unitaires complets couvrant :
  - import de données
  - export de données
  - transformers
  - processors
  - validation de configuration
- Couverture de code élevée

---

### 📚 Documentation

- Ajout d’une documentation complète :
  - concepts
  - exemples simples
  - cas réels (CSV → DB)
  - extension vers base de données
- Clarification du fonctionnement interne du pipeline

