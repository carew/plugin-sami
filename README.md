Sami plugin for [Carew](http://github.com/lyrixx/Carew)
=======================================================

Installation
------------

Install it with composer:

```
composer require carew/plugin-sami:dev-master
```

Then configure `config.yml`

```
engine:
    extensions:
        - Carew\Plugin\Sami\SamiExtension

sami:
    project_dir: /full/path/to/the/library # i.e.:
    src_dir:     src # relative path from the project dir
    name:        Name of your project
    branches:
        master: Master
        newt:   The next release
```

Usage
-----

```
vendor/bin/carew sami:update
```
