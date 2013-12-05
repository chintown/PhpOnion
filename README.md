PhpOnion
========

a simple stack-like backend

Apply PhpOnion as a parent project
----------------------------

add following code at the begining of your main.inc-like file

```php
define('PHP_ONION_ROOT', '/some/path/to/php_onion/');
ini_set('include_path', ini_get('include_path') 
        . ':' . PHP_ONION_ROOT
        . ':' . PHP_ONION_ROOT . 'common'
        . ':' . PHP_ONION_ROOT . 'node_common'
        );
```
