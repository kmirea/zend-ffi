# zend-ffi

Provides the base **API** for creating _extensions_, or modifying `Zend/PHP` internal _core_ with FFI.

It allows loading of shared libraries (`.dll` or `.so`), calling of **C functions** and accessing of **C type** data structures in pure `PHP`.

This package breaks down the `Zend extension API` into _PHP classes_ getting direct access to all PHP internal actions. You can actually change PHP default behaviors. You will be able to get the behavior of [componere](http://docs.php.net/manual/en/intro.componere.php) extension without needing it.

Many routines here is a rewrite of [Z-Engine library](https://github.com/lisachenko/z-engine) package that use different terminology and setup structure. This package follow/keep PHP `C` source code style with a skeleton FFi installation process.

There are many extensions on [Pecl](https://pecl.php.net/package-stats.php) that hasn't been updated.

## Installation

_For normal standalone usage._

```shell
    composer require symplely/zend-ffi
```

_To setup a skeleton for FFI integration._

```shell
    composer create-project symplely/zend-ffi .
```

`FFI` is enabled by default in `php.ini` since `PHP 7.4`, as to `OpCache`, they should not be changed unless already manually disabled.
Only the `preload` section might need setting up if better performance is desired.

```ini
[ffi]
; FFI API restriction. Possible values:
; "preload" - enabled in CLI scripts and preloaded files (default)
; "false"   - always disabled
; "true"    - always enabled
ffi.enable="preload"

; List of headers files to preload, wildcard patterns allowed.
ffi.preload=path/to/.cdef/ffi_preloader.php ; For simple integration with other FFI extensions
; Or
ffi.preload=path/to/vendor/symplely/zend-ffi/preload.php ; For standalone usage

zend_extension=opcache
```

For a simple FFI integration process **create/edit**:

- `ffi_extension.json`  each _package/library_ should list the files to preload, will be process by `ffi_preloader.php` script.
- `.ignore_autoload.php` will be called/executed by `composer create-project your_package .cdef/foldername` event.
  - This event is only called when your _package_ is installed by `composer create-project` command.
- `.preload.php` for general common FFI functions to be used, change the `tag_changeMe` skeleton name.
- `.github\workflows\*.yml` these GitHub Actions is designed for cross-compiling and committing the `binary` back to your **repo**, change `some_lib` and `some_repo` skeleton names.
  - The idea of this is to make installation totally self-contained, the necessary third party library binary is bundled in.
  - The CI build Actions is setup for manually runs only.

```json
// Skeleton for `ffi_extension.json` file
{   // The same name to be used in `composer create-project package .cdef/foldername`
    "name": "foldername",
    // Either
    "preload": {
        "files": [
            "path/to/file.php",
            "...",
            "..."
        ],
    // Or
        "directory": [
            "path/to/directory"
        ]
    }
}
```

## Documentation

The functions in [preload.php](https://github.com/symplely/zend-ffi/blob/main/preload.php) and [Functions.php](https://github.com/symplely/zend-ffi/blob/main/zend/Functions.php) should be used or expanded upon.

For general FFI `C data` handling see **CStruct** [_class_](https://github.com/symplely/zend-ffi/blob/main/zend/CStruct.php).

Functions `c_int_type()`, `c_struct_type()`, `c_array_type()` and `c_typedef()` are wrappers for any _C data typedef_ turning it into PHP **CStruct** class instance, with all **FFI** functions as methods with additional features.

The whole PHP lifecycle process can be achieved by just extending **StandardModule** [_abstract class_](https://github.com/symplely/zend-ffi/blob/main/zend/StandardModule.php).

```php
declare(strict_types=1);

require 'vendor/autoload.php';

final class SimpleCountersModule extends \StandardModule
{
    protected ?string $module_version = '0.4';

    //Represents ZEND_DECLARE_MODULE_GLOBALS macro.
    protected ?string $global_type = 'unsigned int[10]';

    // Do module startup?
    protected bool $m_startup = true;

    // Represents PHP_MINIT_FUNCTION() macro.
    public function module_startup(int $type, int $module_number): int
    {
        echo 'module_startup' . \PHP_EOL;
        return \ZE::SUCCESS;
    }

    // Represents PHP_GINIT_FUNCTION() macro.
    public function global_startup(\FFI\CData $memory): void
    {
        if (\PHP_ZTS) {
            \tsrmls_activate();
        }

        echo 'global_startup' . \PHP_EOL;
        \FFI::memset($this->get_globals(), 0, $this->globals_size());
    }
}

$module = new SimpleCountersModule();
if (!$module->is_registered()) {
    $module->register();
    $module->startup();
}

// Represents ZEND_MODULE_GLOBALS_ACCESSOR() macro.
$data = $module->get_globals();
$data[0] = 5;
$data[9] = 15;
var_dump($data);

ob_start();
phpinfo(8);
$value = ob_get_clean();

preg_match('/simple_counters support => enabled/', $value, $matches);
var_dump($matches[0]);
```

## Reference/Credits

- [Introduction to PHP FFI](https://dev.to/verkkokauppacom/introduction-to-php-ffi-po3)
- [How to Use PHP FFI in Programming](https://spiralscout.com/blog/how-to-use-php-ffi-in-programming)
- [PHP FFI and what it can do for you](https://phpconference.com/blog/php-ffi-and-what-it-can-do-for-you/)
- [Getting Started with PHP-FFI](https://www.youtube.com/watch?v=7pfjvRupoqg) **Youtube**

- [Zend API - Hacking the Core of PHP](https://www.cs.helsinki.fi/u/laine/php/zend.html)
- [PHP at the Core A Hacker's Guide - Manual](http://php.adamharvey.name/manual/en/internals2.php)
- [PHP Internals Book](https://www.phpinternalsbook.com/index.html)
- [Upgrading PHP extensions from PHP5 to NG](https://wiki.php.net/phpng-upgrading)
- [Extending and Embedding PHP](https://flylib.com/books/en/2.565.1/)
- [Whitepaper: Writing PHP Extensions - Zend](https://www.zend.com/sites/zend/files/pdfs/whitepaper-zend-php-extensions.pdf) **PDF**
- [Part V:  Extensibility](http://php.find-info.ru/php/016/part05.html)

- [Awesome PHP FFI](https://github.com/gabrielrcouto/awesome-php-ffi)
- [Z-Engine library](https://github.com/lisachenko/z-engine)

### Possible Security Risks

- [Down the FFI Rabbit Hole](https://pwnfirstsear.ch/2020/07/20/0ctf2020-noeasyphp.html)

### The Beginning

- [About Zeev’s proposal of PHP superset](https://william-pinaud.medium.com/about-zeevs-proposal-of-php-superset-9e291f0de630)

## Contributing

Contributions are encouraged and welcome; I am always happy to get feedback or pull requests on Github :) Create [Github Issues](https://github.com/symplely/uv-ffi/issues) for bugs and new features and comment on the ones you are interested in.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
