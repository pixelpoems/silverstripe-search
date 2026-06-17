# Silverstripe Search Module

[![stability-beta](https://img.shields.io/badge/stability-beta-33bbff.svg)](https://github.com/mkenney/software-guides/blob/master/STABILITY-BADGES.md#beta)

This module provides a Silverstripe search using ajax and configurable indexing.
You can use it in combination with Silverstripe [Elemental](https://github.com/silverstripe/silverstripe-elemental)
and [Fluent](https://github.com/tractorcow-farm/silverstripe-fluent).

## Requirements

* Silverstripe CMS ^6.0
* Silverstripe Framework ^6.0

## Installation

```
composer require pixelpoems/silverstripe-search
```

This module includes:

* `Populate Search Task` — Task to create or update the search index
* `Search Page` — Separate Search Page
* `Inline Search` — Template Include

## Documentation

* [Usage](docs/usage.md) — Inline Search & Search Page
* [Configuration](docs/configuration.md) — Basic config, available variables, index data, populate task
* [Advanced Configuration](docs/advanced.md) — DataObjects, Elemental, Fluent, Toggle
* [Customization](docs/customization.md) — Overwrite templates, modify search results

## Reporting Issues

Please [create an issue](https://github.com/pixelpoems/silverstripe-search/issues) for any bugs you've found, or
features you're missing.

## Credits

Search and Close icons from Feather Icons - https://feathericons.com/