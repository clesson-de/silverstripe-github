# Silverstripe GitHub Module

Promotes GitHub repositories on your website. Repository metadata (name, description, language, stars, forks, latest tag, etc.) is automatically synchronized from the GitHub API — so editors can focus on customizing the presentation with promotion titles, descriptions, and images without manual data entry.

## Features

- **Automatic metadata sync** — a BuildTask (`github-sync`) keeps repository information up to date automatically.
- **Promotion fields** — editors can enhance each repository with a custom title, rich-text description, and image for frontend display.
- **Elemental content element** — the `GitHubRepositoryListElement` renders a filtered, sorted, and limited list of repositories on any page.
- **Multi-account support** — sync and promote repositories from multiple GitHub accounts.
- **Sync log** — every sync run is logged for transparency and debugging.

## Requirements

- PHP 8.3+
- Silverstripe Framework ^6.0
- Silverstripe Admin ^3.0
- DNADesign Elemental ^6.2 or ^7.0
- Guzzle HTTP ^7.0

Silverstripe Fluent is optional. When installed, repository promotion titles, descriptions, and images can be localized separately for each language.

## Supported versions

- SilverStripe 6
- PHP 8.3 or later

## Installation

```bash
composer require clesson-de/silverstripe-github
```

Then run:

```
/dev/build?flush=all
```

## Configuration

The module uses a `SingleRecordAdmin` accessible via **GitHub** in the CMS menu. All configuration is done through the CMS interface.

### GitHub API Token

The sync service requires a personal access token to authenticate with the GitHub API. Configure it in the CMS under **GitHub → Administration → API Token**.

Without a token, API requests are limited to 60 per hour. With a token, the limit increases to 5,000 per hour.

## Usage

### Sync Task

Run the sync manually via CLI:

```bash
vendor/bin/sake dev/tasks/github-sync
```

Or trigger it from the CMS at `/dev/tasks/github-sync`.

The task syncs all repositories for every active account and updates their metadata. Repositories that no longer exist on GitHub are marked with status `deleted`. Editors don't need to enter metadata manually — they can focus on adding promotional content.

> **Note:** Only **public** repositories are synced. Private repositories are automatically skipped during synchronization.

## CMS Structure

The **GitHub** admin section contains three tabs:

| Tab | Description |
|---|---|
| **Repositories** | Overview of all synced repositories. Editors can open each repository to add promotion content. |
| **Synchronization** | Manage GitHub accounts and view sync logs. |
| **Administration** | Module settings. |

## Elemental Content Element

The `GitHubRepositoryListElement` can be added to any page with an Elemental area. It provides:

- **Filter by account** — select one or more GitHub accounts.
- **Filter by language** — select one or more programming languages.
- **Filter by name** — partial match on repository name.
- **Sort field and direction** — choose how repositories are ordered.
- **Limit** — maximum number of repositories to display.

If no filters are configured, the element renders nothing.

## Module Structure

```
silverstripe-github/
├── _config/
│   └── config.yml
├── _config.php
├── composer.json
├── lang/
│   ├── de.yml
│   └── en.yml
├── src/
│   ├── Admins/          # GitHubAdmin (SingleRecordAdmin)
│   ├── Constants/       # RepositoryStatus, RepositorySortField, SortDirection
│   ├── Elements/        # GitHubRepositoryListElement
│   ├── Forms/           # GridFieldConfig classes
│   ├── Models/          # GitHubConfig, GitHubAccount, GitHubRepository, GitHubSyncLog
│   ├── Services/        # GitHubSyncService
│   └── Tasks/           # GitHubSyncTask
├── templates/
│   └── Clesson/
├── LICENSE
└── README.md
```

## License

MIT. See [LICENSE](LICENSE) for details.
