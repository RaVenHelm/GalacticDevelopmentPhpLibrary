![Travis CI](https://travis-ci.org/RaVenHelm/GalacticDevelopmentPhpLibrary.svg?branch=master)


## Galactic Development PHP Library
* PHP Class Library for Utility Tasks in Web Applications
* Compatible with PHP 5.3+

## License
* This library, and subsequent classes, are licensed under the MIT License <https://opensource.org/licenses/MIT>

## Examples
* RegisterVisitor
```php
// Registering a new user to a local SQLite3 Database Instance
use GalacticDevelopment\Sessions\RegisterVisitor;
include 'path\to\file\GalacticDevelopment\Sessions\RegisterVisitor.php';

$db = SQLite3('test.db');
try {
    $register = new RegisterVisitor($db);
    
    // Pass the $_SERVER super-global to get user's IP address
    $register->registerVisitor($_SERVER);
} catch (Exception $ex) {
    // Failed to instantiate database...
} finally {
    if(isset($db)) $db->close();
}
```

* Util#file_build_path
``` php
use GalacticDevelopment\Utility\Util;
include 'path\to\GalacticDevelopment\Utility\Util.php';

// ../public/media/mp3/test.mp3
$path = Util::file_build_path('..', 'media', 'mp3', 'test.mp3');
echo $path;
```