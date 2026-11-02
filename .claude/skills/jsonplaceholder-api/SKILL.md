---
name: jsonplaceholder-api
description: Règles et pièges propres à ce dépôt, une API de type JSONPlaceholder en Symfony 8.1 avec ses pages Twig et l'UI kit `ds`. À utiliser pour toute évolution de ce projet, même si le skill n'est pas cité - ajouter ou modifier une ressource, un endpoint, une entité ou des fixtures, toucher au CORS, au rate limiter ou à la purge nocturne de la sandbox, modifier la page d'accueil, sa console de requêtes ou la documentation `/docs`, changer l'intégration de l'UI kit, ajouter ou retirer une dépendance Composer ou npm.
---

# API JSONPlaceholder (Symfony 8.1)

Ce skill complète `AGENTS.md`, qui reste la référence pour les conventions Symfony générales.
La spec fonctionnelle complète est `sp_cifications_api_jsonplaceholder.md` si elle est présente.

**Ne lis jamais `.env.local`, il contient des secrets.** Pour la configuration, lis uniquement `.env`.

## Réponses aux questions de `AGENTS.md`

Inutile de reposer ces questions, elles sont tranchées :

- **Persistance** : Doctrine ORM 3, avec des migrations pour tout changement de schéma.
- **Interface** : les deux. Une API JSON (Serializer + Validator) et des pages Twig `/` et `/docs`.
- **Authentification** : aucune. L'API est publique.

`form`, `security-bundle`, `mailer`, `notifier`, `web-link`, `expression-language`, Stimulus, Turbo et
AssetMapper ont été retirés volontairement. Ne les réinstalle pas.

## Organisation du code

| Sujet | Emplacement |
| :--- | :--- |
| Lecture et suppression communes | `src/Controller/Api/AbstractResourceController.php` |
| Écritures et routes imbriquées | Un contrôleur par ressource, car `#[MapRequestPayload]` a besoin du type exact |
| Règle seed / donnée utilisateur | `src/Sandbox/ResourceManager.php` |
| Limite par IP, 64 Ko, erreurs JSON, `{}` sur 404 | `src/EventListener/ApiRequestListener.php` |
| Purge | `src/Sandbox/SandboxPurger.php`, `src/Command/SandboxPurgeCommand.php` |
| Seeds | `src/DataFixtures/AppFixtures.php`, à partir de `fixtures/data/*.json` |

Pour ajouter une ressource : entité avec `SandboxEntityTrait` et son index
`idx_{table}_sandbox_cleanup`, DTO d'entrée, contrôleur préfixé par `/api`, classe
dans `SandboxPurger::PURGE_ORDER` (enfants avant parents), puis migration.

## Règles à respecter

- **Routes** : toutes les routes de `src/Controller/Api` commencent par `/api`. `/health-check` et les pages n'en
  font pas partie.
- **Seeds** : un PUT ou PATCH sur un seed répond comme s'il avait réussi, sans écrire en base. Passe toujours par
  `ResourceManager::save()`, sinon cette garantie saute.
- **DELETE** : faux delete pour toutes les données. La route répond 204 sans lire ni modifier la base, même si
  l'entrée n'existe pas. Seule la purge supprime réellement.
- **JSON** : `isSeed` et `createdAt` portent `#[Ignore]` et ne sortent jamais.
- **Validation** : le groupe `full` (POST, PUT) rend les champs obligatoires ; PATCH ne valide que les
  champs envoyés. Un `null` sur PATCH signifie « inchangé ».
- **Clé étrangère inexistante** : 400 via `ResourceManager::reference()`, pas 422.
- **Purge** : elle ne tourne que si un worker est actif (`php bin/console messenger:consume scheduler_default`),
  ou via un crontab système. Elle est calée sur 03h00 Europe/Paris.

## UI kit `ds`

Le kit n'est pas copié dans ce dépôt : `base.html.twig` charge le CSS, les icônes et `main.js` depuis
`DESIGN_SYSTEM_URL`, exposée à Twig par la globale `design_system_url`. La référence est dans
`D:\workspace\sf-p05-design-system` : `docs/design-system-agent-guide.md` et `templates/docs/`.

- Compose avec les classes du kit : pas d'attribut `style`, pas de `onclick`, pas de `<script>` dans une
  page en dehors du script de thème de `base.html.twig`.
- `main.js` étant distant, les modules TS du kit (`Toast`, `Alert`, `themeService`) ne sont pas importables.
  Utilise les attributs `data-ds-*`, l'événement `ds:change` et `core-ts`.
- `core-ts` est documenté dans `D:\workspace\core-ts\docs\`. Garde `fetch` natif dans le « Try it » :
  `fetchAPI` lève une exception sur un non-2xx et peut réessayer, alors que la page montre la réponse brute.
- Plus de 5 options : une liste déroulante plutôt qu'un segmented control, comme le demande le kit.

**Vérifier dans un navigateur.** `lint:*` et un `200` ne prouvent pas que le JS tourne : clique sur le
sélecteur de thème, `data-ui-theme` doit changer sur `<html>`.

Les logos passent par `APP_LOGO` et `APP_FAVICON` de `.env`, exposées à Twig par `logo_path` et `favicon_path`.

## Documentation de l'API

Les pages `/docs` décrivent chaque ressource à partir de `src/Documentation/ApiCatalog.php`. Une nouvelle ressource
ou un nouveau champ doit y être ajouté : un test vérifie que les champs et le payload documentés correspondent à
ce que l'API accepte et renvoie.

**`core-ts` est un outil interne : ne le mentionne jamais dans les pages publiques.** Les exemples de la doc
utilisent `fetch`. Si tu t'en sers dans le code du site, `fetchAPI` réessaie aussi après un 4xx (`retries: 0`) et
`ApiError::getValidationErrors()` lit `errors` alors que l'API renvoie `violations`.

La console de requêtes est le composant `fragments/components/_api_console.html.twig`, utilisable une seule fois
par page avec `js/pages/try-it.js`. Ses exemples viennent de `ApiCatalog::presets()` et `resourcePresets()`.

`/guide` est dépréciée : elle redirige en 301 vers `/docs`. N'y ajoute rien et ne crée pas de lien vers elle.

## Assets du site

| Dossier | Contenu | Git |
| :--- | :--- | :--- |
| `assets/ts/` | Sources TypeScript uniquement : `main.ts`, et `pages/*.ts` pour le code d'une seule page | versionné |
| `assets/scss/` | Sources SCSS uniquement : chaque `*.scss` de premier niveau hors partials donne un fichier CSS | versionné |
| `public/js/`, `public/css/` | Fichiers générés, à ne jamais modifier à la main | ignoré |

`npm run dev` compile en continu et `npm run build` produit la version de production avec le contrôle de
types. Ces fichiers étant ignorés par git, un déploiement doit lancer `npm ci && npm run build`.
Ne place rien d'autre dans `public/js/` : `compile-ts.js` y supprime tout ce qui ne sort pas du build.

## Pièges rencontrés

- **Lock** : `LOCK_DSN` pointe vers `var/lock`, car Apache ne peut pas écrire dans le dossier temporaire de
  Windows. Le limiter étant injecté dans `ApiRequestListener`, une erreur de lock casse toutes les pages.
- **Base de données** : avant une migration ou `doctrine:fixtures:load`, vérifie la base visée par la commande,
  sans lire `.env.local`. Une erreur de table manquante sur `sf-react-cms` signale une mauvaise `DATABASE_URL`.
- **CORS** : `paths: '^/': null` de la recette annule `allow_origin`, et `allow_origin: ['*']` seul renvoie
  l'origine de la requête. `forced_allow_origin_value: '*'` est ce qui produit `*`.
- **`.env.local` n'est pas lu en environnement de test** : les URL de base de test sont dans
  `.env.test.local`, et le suffixe `_test` est ajouté par `doctrine.yaml`.
- **Rate limiter en test** : le pool `array` est vidé entre deux requêtes, même avec `disableReboot()`.
  `ApiTestCase::setUp()` vide donc un pool fichier, avec une limite de 20 appels par test.
- **`allow-contrib` vaut `false`** : une recette contrib est ignorée sans erreur (ligne `IGNORING` de
  Composer). Active-le, lance `composer recipes:install <paquet> --force`, puis remets `false`.
- **Retirer un paquet** peut laisser des références dans `config/packages/` et faire échouer `cache:clear`.

## Tests

Avant le premier lancement : `php bin/console doctrine:database:create --env=test`, puis
`doctrine:migrations:migrate --env=test`. Lance ensuite `vendor/bin/phpunit`. `dama/doctrine-test-bundle`
isole chaque test dans une transaction. Crée les données avec les aides de `tests/ApiTestCase.php` : les
tests ne dépendent pas des seeds.
