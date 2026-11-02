# Dépendances — JSONPlaceholder API (Symfony 8.1)

> **Base de comparaison** : ton `composer.json` (PHP ≥ 8.4, Symfony 8.1.*, squelette webapp).
>
> Légende : ✅ déjà dans ton `composer.json` · ➕ à installer · ❌ à désinstaller · ⚪ optionnel

---

## 1. Nettoyage : retirer Symfony UX et Stimulus

L'UI kit embarque son propre JS (`main.js`, piloté par les attributs `data-ds-*`), donc
Stimulus et Turbo ne servent à rien.

### 1.1 Paquets Composer

Ton `composer.json` en contient deux :

| Paquet | Version actuelle | Action |
| :--- | :--- | :--- |
| `symfony/stimulus-bundle` | `^3.6` | ❌ Contrôleurs Stimulus, remplacés par `data-ds-*` |
| `symfony/ux-turbo` | `^3.6` | ❌ Navigation Turbo, inutile ici |

```bash
composer remove symfony/stimulus-bundle symfony/ux-turbo
```

Aucun autre paquet `symfony/ux-*` n'est présent. Tu peux le confirmer après la suppression :

```bash
composer show | grep -E "symfony/(ux-|stimulus)"
```

### 1.2 Entrées de l'importmap

La désinstallation via Flex ne nettoie pas toujours `importmap.php` :

```bash
php bin/console importmap:remove @hotwired/stimulus @hotwired/turbo
```

### 1.3 Fichiers résiduels

```bash
rm -rf assets/controllers assets/controllers.json assets/bootstrap.js
```

Dans `assets/app.js`, supprime la ligne `import './bootstrap.js';`.

### 1.4 Vérification

```bash
grep -rnE "stimulus|turbo|ux_|data-controller" assets/ templates/ config/ importmap.php
php bin/console cache:clear
```

La recherche ne doit plus rien renvoyer.

---

## 2. Dépendances du projet

### 2.1 Base de données — Doctrine ORM 3

| Paquet | Statut | Rôle |
| :--- | :---: | :--- |
| `doctrine/orm` | ✅ | ORM |
| `doctrine/doctrine-bundle` | ✅ | Intégration Symfony |
| `doctrine/doctrine-migrations-bundle` | ✅ | Migrations |

Il faut aussi l'extension PHP du pilote (`pdo_pgsql` ou `pdo_mysql`). Ce n'est pas un paquet Composer.

### 2.2 API : sérialisation et validation

| Paquet | Statut | Rôle |
| :--- | :---: | :--- |
| `symfony/serializer` | ✅ | JSON, `#[Ignore]` sur `is_seed` / `created_at` |
| `symfony/property-access` | ✅ | Requis par le Serializer |
| `symfony/property-info` | ✅ | Requis par le Serializer |
| `phpdocumentor/reflection-docblock` | ✅ | Typage des objets imbriqués (`address`, `geo`, `company`) |
| `symfony/validator` | ✅ | `NotBlank`, `Length`, `Email`, `Url` |

### 2.3 Sécurité et CORS

```bash
composer require nelmio/cors-bundle symfony/rate-limiter symfony/lock
```

| Paquet | Statut | Rôle |
| :--- | :---: | :--- |
| `nelmio/cors-bundle` | ➕ | `CORS: *` |
| `symfony/rate-limiter` | ➕ | Limite par IP → `429` |
| `symfony/lock` | ➕ | Empêche les requêtes concurrentes de contourner la limite |

### 2.4 CRON de purge

**Option A — Symfony Scheduler** (`#[AsCronTask('0 3 * * *')]`) :

```bash
composer require symfony/scheduler dragonmantank/cron-expression
```

| Paquet | Statut | Rôle |
| :--- | :---: | :--- |
| `symfony/scheduler` | ➕ | Planification |
| `dragonmantank/cron-expression` | ➕ | **Obligatoire** pour interpréter `'0 3 * * *'` |
| `symfony/messenger` | ✅ (indirect) | Installé via `symfony/doctrine-messenger`. Le Scheduler tourne via un worker : `messenger:consume scheduler_default` |

**Option B — Crontab système** : aucun paquet nécessaire.

```cron
0 3 * * * cd /chemin/projet && php bin/console app:sandbox:purge --env=prod
```

### 2.5 Logs de purge

| Paquet | Statut | Rôle |
| :--- | :---: | :--- |
| `symfony/monolog-bundle` | ✅ | Canal dédié `sandbox_purge` → `sandbox-purge.log` |

### 2.6 Interface : pages `/` et `/guide` avec l'UI kit

| Paquet | Statut | Rôle |
| :--- | :---: | :--- |
| `symfony/twig-bundle` | ✅ | Templates |
| `twig/extra-bundle` | ✅ | Filtres utiles (`format_*`, etc.) |
| `symfony/asset` | ✅ | Fonction `asset()` |
| `symfony/asset-mapper` | ✅ | Versionnage des fichiers du kit, sans Node ni build |

Aucune lib Markdown côté serveur n'est nécessaire : l'UI kit convertit lui-même les blocs
`[data-markdown]` dans le navigateur.

### 2.7 Fausses données (fixtures)

```bash
composer require --dev orm-fixtures fakerphp/faker
```

| Paquet | Statut | Rôle |
| :--- | :---: | :--- |
| `doctrine/doctrine-fixtures-bundle` | ➕ dev | Commande `doctrine:fixtures:load` |
| `fakerphp/faker` | ➕ dev | Génération des données |
| `zenstruck/foundry` | ⚪ dev | Factories et relations |

Pour obtenir les données exactes de JSONPlaceholder, importe les JSON d'origine depuis
`fixtures/data/*.json` au lieu de les générer avec Faker.

### 2.8 Outils de développement et tests

| Paquet | Statut | Rôle |
| :--- | :---: | :--- |
| `symfony/maker-bundle` | ✅ dev | Génération de code |
| `symfony/debug-bundle` / `symfony/web-profiler-bundle` / `symfony/stopwatch` | ✅ dev | Débogage |
| `phpunit/phpunit` (`^13.4`) / `symfony/browser-kit` / `symfony/css-selector` | ✅ dev | Tests |
| `dama/doctrine-test-bundle` | ➕ dev | Rollback automatique de la BDD entre les tests |

---

## 3. Paquets présents mais probablement inutiles

L'API est publique, sans compte utilisateur, sans formulaire HTML et sans envoi de mail.
Avant de supprimer ces paquets, vérifie qu'aucun code ne s'en sert.

| Paquet | Raison | Conseil |
| :--- | :--- | :--- |
| `symfony/form` | Les payloads JSON passent par le Serializer + Validator | ❌ |
| `symfony/security-bundle` | Aucune authentification (le rate limiter n'en dépend pas) | ❌ |
| `symfony/mailer` | Aucun envoi de mail | ❌ |
| `symfony/notifier` | Aucune notification | ❌ |
| `symfony/web-link` | Préchargements HTTP/2, non utilisés | ❌ |
| `symfony/expression-language` | Surtout utilisé par la sécurité | ⚪ à retirer avec `security-bundle` |
| `symfony/translation` / `symfony/intl` | Utiles seulement pour un site multilingue | ⚪ |
| `symfony/http-client` | Aucun appel HTTP sortant prévu | ⚪ pratique pour les tests fonctionnels |
| `symfony/process` | Aucun sous-processus | ⚪ |

À **conserver** même s'ils semblent superflus :

- `symfony/mime` : pas seulement lié au mailer, il sert aussi à la détection de types.
- `phpstan/phpdoc-parser` et `phpdocumentor/reflection-docblock` : PropertyInfo s'en sert pour typer les objets imbriqués lors de la désérialisation.
- `symfony/doctrine-messenger` : il apporte Messenger, nécessaire au Scheduler.
- `symfony/asset-mapper` : il sert l'UI kit (voir section 4).

```bash
composer remove symfony/form symfony/security-bundle symfony/mailer symfony/notifier symfony/web-link symfony/expression-language
```

Après la suppression de `security-bundle`, vérifie que `config/packages/security.yaml`
a bien disparu, et qu'aucun `is_granted()` ou `app.user` ne reste dans les templates.

---

## 4. Intégration de l'UI kit

### 4.1 Emplacement des fichiers

```text
assets/
└── vendor/ds/
    ├── ds-ui-kit.css
    ├── ds-icons.css
    ├── main.js
    └── chunks/
        └── chunk-C56PSAD5.js   ← importé par main.js, à récupérer
```

> ⚠️ `main.js` importe `./chunks/chunk-C56PSAD5.js` (fonctions `$`, `convertMarkdownToHtml`,
> `initClipboardHandlers`). Ce fichier n'était pas joint : sans lui, le kit ne s'initialise pas.

### 4.2 Point d'entrée `assets/app.js`

```js
import './vendor/ds/ds-ui-kit.css';
import './vendor/ds/ds-icons.css';
import './vendor/ds/main.js';

// Code propre au site (page « Try it », etc.)
```

AssetMapper résout l'import relatif du chunk et versionne les fichiers.

### 4.3 Polices d'icônes

`ds-icons.css` charge ses polices depuis `https://design-system.dev.frederickagathe.fr`.
Comme les polices sont chargées en cross-origin, ce serveur doit renvoyer
`Access-Control-Allow-Origin` pour le domaine de l'API. Sinon, les icônes s'afficheront en carrés vides.
Pour ne plus dépendre de ce serveur, tu peux rapatrier les polices dans `assets/vendor/ds/fonts/`
et réécrire les `url()`.

### 4.4 Gabarit `templates/base.html.twig`

Le script inline dans le `<head>` applique le thème avant le premier rendu. Sans lui, `main.js`
(chargé en module, donc différé) provoque un flash clair → sombre.

```twig
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{% block title %}JSONPlaceholder API{% endblock %}</title>
    <script>
        (() => {
            let mode = 'auto';
            try { mode = localStorage.getItem('ds-theme') || 'auto'; } catch (e) {}
            const dark = mode === 'dark'
                || (mode === 'auto' && matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.setAttribute('data-ui-theme', dark ? 'dark' : 'light');
        })();
    </script>
    {% block importmap %}{{ importmap('app') }}{% endblock %}
</head>
<body>
<div class="layout-app">
    <header class="app-header">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="{{ path('app_home') }}">
                    <i class="mdi mdi-api"></i> JSONPlaceholder
                </a>
                <button class="navbar-toggler" data-ds-toggle="collapse"
                        data-ds-target="#main-nav" aria-expanded="false" aria-label="Menu">
                    <i class="ri-menu-line"></i>
                </button>
                <div class="navbar-collapse collapse" id="main-nav">
                    <ul class="navbar-nav">
                        <li><a class="nav-link" href="{{ path('app_home') }}">Ressources</a></li>
                        <li><a class="nav-link" href="{{ path('app_guide') }}">Guide</a></li>
                    </ul>
                </div>
                <div class="navbar-end">
                    <div class="segmented-control segmented-control-sm"
                         role="radiogroup" aria-label="Thème" data-ds-theme-switch>
                        <button class="segmented-control-option" role="radio"
                                data-value="light" aria-checked="false" aria-label="Clair">
                            <i class="ri-sun-line"></i>
                        </button>
                        <button class="segmented-control-option" role="radio"
                                data-value="auto" aria-checked="true" aria-label="Automatique">
                            <i class="ri-contrast-2-line"></i>
                        </button>
                        <button class="segmented-control-option" role="radio"
                                data-value="dark" aria-checked="false" aria-label="Sombre">
                            <i class="ri-moon-line"></i>
                        </button>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <main class="app-main">
        <div class="container">
            {% block body %}{% endblock %}
        </div>
    </main>

    <footer class="app-footer footer">
        <div class="container">
            <div class="footer-bottom">
                <span>Données sandbox purgées chaque nuit à 03h00</span>
                <a href="{{ path('api_health_check') }}">Health check</a>
            </div>
        </div>
    </footer>
</div>
</body>
</html>
```

### 4.5 Correspondance besoin → composant du kit

| Besoin de la doc | Composant UI kit |
| :--- | :--- |
| Exemples `fetch(url, data)` | `.code-block` + `.code-header` + `.code-content` (bouton copier géré par le kit) |
| Liste des 6 ressources | `.card` / `.grid .grid-cols-md-3` |
| Méthodes HTTP | `.badge-soft-success` (GET), `-info` (POST), `-warning` (PUT/PATCH), `-danger` (DELETE) |
| Matrice des endpoints | `.table .table-hover` dans `.table-responsive` |
| Sélecteur de ressource du « Try it » | `.segmented-control` (événement `ds:change`) |
| Réponse JSON / erreurs | `.code-content` + `.alert-soft-danger` |
| Statut du health check | `.badge-solid-success` / `.badge-solid-danger` |
| Sections du guide | `.accordion` (`data-ds-toggle="accordion"`) |
| Notifications (copie, erreurs réseau) | `.toast` |

Le « Try it » se fait en JavaScript natif (`fetch`, puis écoute de `ds:change` sur le
segmented control). Aucun framework front n'est nécessaire.

---

## 5. Récapitulatif des commandes

```bash
# 1. Nettoyage UX / Stimulus
composer remove symfony/stimulus-bundle symfony/ux-turbo
php bin/console importmap:remove @hotwired/stimulus @hotwired/turbo
rm -rf assets/controllers assets/controllers.json assets/bootstrap.js

# 2. Dépendances manquantes
composer require nelmio/cors-bundle symfony/rate-limiter symfony/lock
composer require symfony/scheduler dragonmantank/cron-expression   # si option Scheduler

# 3. Dev
composer require --dev orm-fixtures fakerphp/faker dama/doctrine-test-bundle

# 4. Optionnel : alléger le squelette webapp
composer remove symfony/form symfony/security-bundle symfony/mailer symfony/notifier symfony/web-link symfony/expression-language
```

### État cible du `require` (hors dev)

```json
"require": {
    "php": ">=8.4",
    "ext-ctype": "*",
    "ext-iconv": "*",
    "doctrine/doctrine-bundle": "^3.3",
    "doctrine/doctrine-migrations-bundle": "^4.0",
    "doctrine/orm": "^3.7",
    "dragonmantank/cron-expression": "...",
    "nelmio/cors-bundle": "...",
    "phpdocumentor/reflection-docblock": "^6.0",
    "phpstan/phpdoc-parser": "^2.4",
    "symfony/asset": "8.1.*",
    "symfony/asset-mapper": "8.1.*",
    "symfony/console": "8.1.*",
    "symfony/doctrine-messenger": "8.1.*",
    "symfony/dotenv": "8.1.*",
    "symfony/flex": "^2",
    "symfony/framework-bundle": "8.1.*",
    "symfony/lock": "8.1.*",
    "symfony/mime": "8.1.*",
    "symfony/monolog-bundle": "^3.0|^4.0",
    "symfony/property-access": "8.1.*",
    "symfony/property-info": "8.1.*",
    "symfony/rate-limiter": "8.1.*",
    "symfony/runtime": "8.1.*",
    "symfony/scheduler": "8.1.*",
    "symfony/serializer": "8.1.*",
    "symfony/string": "8.1.*",
    "symfony/twig-bundle": "8.1.*",
    "symfony/validator": "8.1.*",
    "symfony/yaml": "8.1.*",
    "twig/extra-bundle": "^2.12|^3.0",
    "twig/twig": "^2.12|^3.0"
}
```

Les `...` seront remplis par Composer au moment du `require` (la config `bump-after-update` est active).
`translation`, `intl`, `http-client` et `process` n'y figurent pas, puisqu'ils sont optionnels.
