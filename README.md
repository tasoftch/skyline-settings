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

