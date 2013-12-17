PhpOnion
========

a simple stack-like backend

Apply PhpOnion as a parent project
----------------------------

add following code at the beginning of your main.inc-like file

```php
$project_path = realpath(dirname(__FILE__).'/../');
ini_set('include_path', ini_get('include_path') . ':' . $project_path);
require '/some/path/to/PhpOnion/core/main.inc.php';
```
