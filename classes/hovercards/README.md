# Hovercards Module

This module controls the integration with [Hovercards](https://github.com/Automattic/gravatar/tree/trunk/web/packages/hovercards).

## Hooks


### Disabling the module

If for whatever reason you want to disable this specific part of the plugin, you can do so by using the following hook:
```php
add_filter( 'gravatar_enhanced_hovercards_module_enabled', '__return_false' );
```
