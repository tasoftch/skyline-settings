# Skyline Settings
This package extends your Skyline Application by a setting tool that provides a persistent settings storage.

#### Installation
````bin
$ composer require skyline/settings
````

#### Usage
Compiling with Skyline, this package adds a ```settingManager``` service.
`````php
<?php
use TASoft\Service\ServiceManager;
use Skyline\Setting\SettingManagerInterface;

$sm = ServiceManager::generalServiceManager()->get( SettingManagerInterface::SERVICE_NAME );
// or
$sm = ServiceManager::generalServiceManager()->get( "settingManager" );

// In an action controller method, just use:
$sm = $this->settingManager;
`````

### The Settings
The settings are designed as key value pair, where the key is the setting name and the value its value.  
```php
<?php
use Skyline\Setting\SettingManagerInterface;
/** @var SettingManagerInterface $sm */

$width = $sm->getSetting("width");
```

The settings mechanism knows three locations, where a setting can be defined:
1. The default settings scope.  
    Once declared, the setting is valid in the whole application (like the example above)
1. The grouped settings scope.  
    The setting is part of a group (ex. VIEW.width or EDITOR.width)
1.  The user settings scope.  
    Any grouped or default setting can be associated with a user so then the setting VIEW.width for user A is different than VIEW.width for user B.
    
If the scope is not selected or the setting does not exist in it, the setting manager will try to find it in parent scope.  
Example: I'm looking for setting ``itemsPerPage`` in group ``DASHBOARD`` of ``USER_A``  
The setting manager will return the first existing setting:
-   Setting DASHBOARD.itemsPerPage for USER_A
-   Setting DASHBOARD.itemsPerPage
-   Setting itemsPerPage for USER_A
-   Setting itemsPerPage
-   NULL

Example: I'm looking for setting ``itemsPerPage`` of ``USER_A`` (without group)
-   Setting itemsPerPage for USER_A
-   Setting itemsPerPage
-   NULL

Please note that setting groups and user groups is not the same.

The manager also allows to declare settings:
```php
<?php
use Skyline\Setting\SettingManagerInterface;
/** @var SettingManagerInterface $sm */

// Declare width only in default scope
$sm->declareSetting('width', 250);

// Declare width only in group VIEW
$sm->declareSetting("width", 250, 'VIEW');

// Declare width only for USER_A
$sm->declareSetting("width", 250, NULL, 'USER_A');

// Declare width only in group VIEW for USER_A
$sm->declareSetting("width", 250, 'VIEW', 'USER_A');
```

In the same way you can remove settings:
```php
<?php
use Skyline\Setting\SettingManagerInterface;
/** @var SettingManagerInterface $sm */

// Removes width only from default scope
$sm->removeSetting('width');

// Removes width only from group VIEW
$sm->removeSetting("width", 'VIEW');

// Removes width only for USER_A
$sm->removeSetting("width", NULL, 'USER_A');

// Removes width only from group VIEW for USER_A
$sm->removeSetting("width", 'VIEW', 'USER_A');

// Removes all settings with name width
$sm->removeSettingAll("width");
```