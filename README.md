# Composer Preload Files Plugin

[![GitHub release](https://img.shields.io/github/release/codezero-be/composer-preload-files.svg?style=flat-square)](https://github.com/codezero-be/composer-preload-files/releases)
[![License](https://img.shields.io/packagist/l/codezero/composer-preload-files.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/codezero/composer-preload-files.svg?style=flat-square)](https://packagist.org/packages/codezero/composer-preload-files)

## Autoload Your Files Before Vendor Files

This Composer plugin enables you to autoload files that you specify before any vendor files.

This package is based on the original [funkjedi/composer-include-files](https://github.com/funkjedi/composer-include-files) by [@funkjedi](https://github.com/funkjedi) and its fork [hopeseekr-contribs/composer-include-files](https://github.com/hopeseekr-contribs/composer-include-files) by [@hopeseekr](https://github.com/hopeseekr).
Because maintenance of these packages appears to be stalled, I decided to attempt and remake the package from scratch and fix any reported bugs in the process.

## ✅ Requirements

- PHP >= 7.0
- Composer ^2.0

## 📦 Install

Install this package with Composer:

```bash
composer require codezero/composer-preload-files
```

## 📘 Usage

Add the `preload-files` to your project's `composer.json` under the `extra` section:

```json
"extra": {
    "preload-files": [
        "app/helpers.php"
    ]
},
```

The `preload-files` in the `extra` section will be loaded before the `files` in a standard `autoload` or `autoload-dev` section.
This is true for your project, but also for any vendor package. Your project's preload files will always be loaded first.

## 🔌 Example Use Case

The best example use case is when you need to override a global helper function in a [Laravel](https://laravel.com) project.
Those helper functions are declared in helper files that are loaded in the `files` array in the `autoload` section of `composer.json`:

```json
"autoload": {
    "files": [
        "src/Illuminate/Collections/helpers.php",
        "src/Illuminate/Events/functions.php",
        "src/Illuminate/Foundation/helpers.php",
        "src/Illuminate/Support/helpers.php"
    ]
},
```

These functions are declared like this:

```php
// helpers.php
if ( ! function_exists('route')) {
    function route($name, $parameters = [], $absolute = true)
    {
        return app('url')->route($name, $parameters, $absolute);
    }
}
```

If you add your own helper file to your project's `autoload` section to override such function, you will notice that Laravel's function is already loaded, and you can not redeclare it.

One way to solve this, is to manually `require` the helper file before Composer's `autoload.php` file.
For Laravel, this means you need to `require` the file in your project's `public/index.php` file:

```php
require __DIR__.'/../app/helpers.php';
require __DIR__.'/../vendor/autoload.php';
```

This works, but it is difficult, if not impossible to test (I did not find a way yet).
If you are developing a package, it's also an extra step that users need take to install it.

Another solution is a package like this.

## ☕ Credits

- [@ivanvermeyen](https://github.com/ivanvermeyen)
- [@hopeseekr](https://github.com/hopeseekr) - original fork: [hopeseekr-contribs/composer-include-files](https://github.com/hopeseekr-contribs/composer-include-files)
- [@funkjedi](https://github.com/funkjedi) - original: [funkjedi/composer-include-files](https://github.com/funkjedi/composer-include-files)
- [All contributors](https://github.com/codezero-be/composer-preload-files/contributors)

## 🔒 Security

If you discover any security related issues, please [e-mail me](mailto:ivan@codezero.be) instead of using the issue tracker.

## 📑 Changelog

A complete list of all notable changes to this package can be found on the
[releases page](https://github.com/codezero-be/composer-preload-files/releases).

## 📜 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
