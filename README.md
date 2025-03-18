# phpstorm-inspect

This package enables you to comfortably use PhpStorm as a CLI tool for static analysis.

This is a modernized version of [shopsys/phpstorm-inspect](https://github.com/shopsys/phpstorm-inspect) updated for PHP 8.3.

## Features

* Wrapper for PhpStorm's `inspect.sh` script with additional functionality
* Waits for PhpStorm to finish already running inspections
* Clears PhpStorm's cache before every run to prevent stale cache issues
* Parses XML output generated by PhpStorm and presents it in a readable form
* Supports both text and checkstyle output formats
* Modern PHP 8.3 implementation with Symfony Console integration

## Installation

```bash
composer require shopsys/phpstorm-inspect
```

## Usage

```bash
vendor/bin/phpstorm-inspect inspect \
  --inspect-sh=/path/to/PhpStorm/bin/inspect.sh \
  --system-path=/path/to/.WebIde*/system \
  --project-path=/path/to/project \
  --profile=/path/to/project/.idea/inspectionProfiles/Project_Default.xml \
  --directory=/path/to/inspect \
  --format=text
```

### Arguments

* `--inspect-sh`: Path to `inspect.sh` script
* `--system-path`: Path to `.WebIde*/system` directory
* `--project-path`: Path to project directory (that contains `.idea` directory)
* `--profile`: Path to inspection profile XML file
* `--directory`: Path in which are the inspected sources
* `--format`: Format of output result, accepted values: "text" (default) / "checkstyle"

## License

MIT License (see LICENSE file)
