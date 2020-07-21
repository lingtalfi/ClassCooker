ClassCooker
===========
2017-04-11 -> 2020-07-21


A tool to cook your class: add/remove methods, properties, stuff like that.


This is part of the [universe framework](https://github.com/karayabin/universe-snapshot).


Install
==========
Using the [uni](https://github.com/lingtalfi/universe-naive-importer) command.
```bash
uni import Ling/ClassCooker
```

Or just download it and place it where you want otherwise.









Raw example
=============
2017-04-11


Straight from my working file.

```php
<?php

use Ling\ClassCooker\ClassCooker;

require_once __DIR__ . "/../boot.php";
require_once __DIR__ . "/../init.php";


header("content-type: text/plain");

$f = '/myphp/kaminos/app/hachis.txt';
$f = '/myphp/kaminos/app/class-core/Services/X.php';
a(ClassCooker::create()->setFile($f)->getMethodsBoundaries());
a(ClassCooker::create()->setFile($f)->getMethodsBoundaries(['protected', 'static']));
a(ClassCooker::create()->setFile($f)->getMethods(['protected', 'static']));
a(ClassCooker::create()->setFile($f)->getMethodBoundariesByName("Connexion_foo"));
//a(ClassCooker::create()->setFile($f)->removeMethod("Connexion_foo"));

$content = ClassCooker::create()->setFile($f)->getMethodContent("Connexion_foo");
a($content);
$newContent = str_replace('Connexion_foo', 'Connexion_shoo', $content);
a(ClassCooker::create()->setFile($f)->addMethod("Connexion_shoo", $newContent));
a(ClassCooker::create()->setFile($f)->updateMethodContent("Core_webApplicationHandler", function ($content) {
    return $content .  "\t\t// oooo" . PHP_EOL;
}));

```








History Log
------------------
    
- 1.9.0 -- 2020-07-21

    - add ClassCooker->addContent method
    
- 1.8.2 -- 2020-07-17

    - fake test commit to test uni2 (2)
    
- 1.8.1 -- 2020-07-17

    - fake test commit to test uni2
    
- 1.8.0 -- 2020-07-10

    - add ClassCooker->updatePropertyComment methods and some other methods
    
- 1.7.0 -- 2018-03-25

    - add ClassCookerHelper::createSectionComment method
    
- 1.6.0 -- 2018-03-25

    - add ClassCookerHelper::getMethodsBoundaries method
    
- 1.5.0 -- 2018-03-25

    - add ClassCookerHelper class
    
- 1.4.1 -- 2017-04-23

    - fix getMethods returning commented methods
    
- 1.4.0 -- 2017-04-11

    - add getMethodSignature method
    
- 1.3.0 -- 2017-04-11

    - fix addMethod handles the case of an empty class
    
- 1.2.0 -- 2017-04-11

    - fix ignore commented methods
    
- 1.1.0 -- 2017-04-11

    - add includeWrap argument to getMethodContent
    
- 1.0.0 -- 2017-04-11

    - initial commit