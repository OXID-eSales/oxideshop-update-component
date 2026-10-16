# Change Log for OXID eShop update component

## v3.0.0 - Unreleased

### Added

- Functionality for upgrading the OXID eShop compilation from v7.x to the next major version
- `oe:update:migrate-theme-metadata` command to migrate a theme's `theme.php` to `metadata.yaml` and `config.yaml`
- `oe:update:migrate-theme-configuration` command to migrate theme settings and the active theme from the `oxconfig` table to theme YAML configuration, including renamed standard theme settings
- `oe:update:migrate-theme-templates` command to replace `getViewThemeParam()` calls in templates with the typed theme setting service
- `oe:update:remove-theme-configuration` command to remove theme configuration data from the `oxconfig` and `oxconfigdisplay` tables

### Removed

- Functionality for upgrading the OXID eShop compilation from v6.x to next major version