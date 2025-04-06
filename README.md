# Laravel Package Maker

A simple CLI tool to generate Laravel package skeletons.

## Installation

```bash
composer global require jabsa-infotech/package-maker
```

Or install locally in your project:

```bash
composer require --dev jabsa-infotech/package-maker
```

## Usage

```bash
make-package
```

Follow the prompts to generate your package.

## Package Structure

The generator creates the following structure:

```
packages/
└── your-package-name/
    ├── composer.json
    ├── LICENSE.md
    ├── phpunit.xml.dist
    ├── README.md
    ├── src/
    │   ├── YourPackage.php
    │   ├── YourPackageServiceProvider.php
    │   └── Facades/
    │       └── YourPackage.php
    └── tests/
        └── PackageTest.php
```