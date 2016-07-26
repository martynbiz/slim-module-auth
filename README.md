# Core Module #

## Introduction ##

This is the core dependencies, classes, etc for a Slim module app.

## Installation ##

Use composer to fetch the files and copy files to your project tree:

```
$ composer require martynbiz/slim-module-core
```

Enable the module in src/settings.php:

```
return [
    'settings' => [
        ...
        'module_initializer' => [
            'modules' => [
                ...
                'martynbiz-core' => 'MartynBiz\\Slim\\Module\\Core\\Module',
            ],
        ],
    ],
];
```

## Usage ##

This core application has many dependencies

### Current user ###

App middleware will check if the user is signed in and attach the user to the templates
data. This object can be used in templates:

```html
<?php if ($this->currentUser): ?>
    Hello <?= $this->currentUser->first_name ?>
<?php endif; ?>
```
