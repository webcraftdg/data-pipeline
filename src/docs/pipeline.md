# PIPELINE

Le pipeline orchestre la transformation des données.

## FLUX

Input → Mapping → Transformers → Processor → Output

## RESPONSABILITÉS

- orchestrer le flux
- appliquer le mapping
- appliquer les transformations
- exécuter le processor
- écrire le résultat

## IMPORTANT

Le pipeline ne connaît pas :

- la base de données
- le métier
- les frameworks

Il ne fait que transformer des données.

[Accueil](../../README.md)