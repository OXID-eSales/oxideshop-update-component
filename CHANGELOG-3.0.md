# Change Log for OXID eShop update component

## v3.0.0 - Unreleased

### Added

- Functionality for upgrading the OXID eShop compilation from v7.x to the next major version
- `oe:update:migrate-theme-metadata` command to migrate a theme's `theme.php` to `metadata.yaml` and `config.yaml`
- `oe:update:migrate-theme-configuration` command to migrate theme settings and the active theme state from the `oxconfig` table to the theme YAML configuration
- `oe:update:migrate-theme-templates` command to rewrite `getViewThemeParam()` template calls to the typed theme setting service
- `oe:update:remove-theme-configuration` command to remove theme configuration data from the `oxconfig` table
- The theme migration commands rename OXID's standard theme settings to their modernized version

### Removed

- Functionality for upgrading the OXID eShop compilation from v6.x to next major version