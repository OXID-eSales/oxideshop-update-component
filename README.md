OXID eShop update component
===========================

Component for upgrading OXID eShop compilation from v6.1 to v6.2.

## Installation

Run the following command to install the component:

```bash
composer require oxid-esales/oxideshop-update-component
```

## Usage

To get list of commands execute:

```bash
vendor/bin/oe-console | grep oe:oxideshop-update-component
```

## Running tests

To run tests for the component please define OXID eShop bootstrap file:

```bash
vendor/bin/phpunit --bootstrap=../source/bootstrap.php tests/
```

## Bugs and Issues

If you experience any bugs or issues, please report them in the section **OXID eShop** of https://bugs.oxid-esales.com.

## License

See LICENSE file for license details.
