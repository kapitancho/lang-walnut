# Walnut Language v2
The follow-up of the Cast and the Walnut languages.

## Installation

To install the latest version, use the following command:

```bash
$ composer require walnut/lang
```

## Usage

Walnut is a programming language which you can easily call from any PHP code. 
You can use the `CliEntryPoint` for interactions between the language and the host environment.

Sample usage:

```php
use Walnut\Lang\Implementation\Program\EntryPoint\Cli\CliEntryPointFactory;
$rootDir = __DIR__; //or something different
$sourceRoot = $rootDir . '/nut-src';
$packages = ['core' => $rootDir . '/vendor/walnut/lang/core-nut-lib'];

$result = new CliEntryPointFactory($sourceRoot, $packages)
    ->entryPoint->call('start', 'arg1', 'arg2'); //Call the main function of the `start` module
echo "Result: $result", PHP_EOL;
```

## Demos
- [Downloadable Walnut Lang demos](https://github.com/kapitancho/lang-walnut-demos)
- [Online demos](https://demo.walnutphp.com/)

## Documentation
0. [Index](docs/00-language-reference.md)
1. [Introduction](docs/01-introduction.md)
2. [Types and Values](docs/02-types-and-values.md) 
3. [Functions](docs/03-functions.md)
4. [Expressions](docs/04-expressions.md)
5. [Method Reference](docs/05-method-reference.md)
