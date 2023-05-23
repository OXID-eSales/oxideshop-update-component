OXID eShop update component
===========================

This component is a helper for updating the OXID eShop compilation from v6.5 to v7.0.

## Installation

 - run the following command to install the component:

```bash
composer require oxid-esales/oxideshop-update-component:^v2.0.0
```

 - clear the eShop cache.

## How to install component for development?

Checkout component besides OXID eShop `source` directory:

```bash
git clone https://github.com/OXID-eSales/oxideshop-update-component.git
```

Run composer install command:

```bash
cd oxideshop-update-component
composer install
```

Add dependency to OXID eShop `composer.json` file:

```bash
composer config repositories.oxid-esales/oxideshop-update-component path oxideshop-update-component
composer require --dev oxid-esales/oxideshop-update-component:*
```

## How to run tests?

To run tests for the component please define OXID eShop bootstrap file:

```bash
vendor/bin/phpunit --bootstrap=../source/bootstrap.php tests/
```

## Bugs and Issues

If you experience any bugs or issues, please report them in the section **OXID eShop** of https://bugs.oxid-esales.com.

## License

See LICENSE file for license details.
