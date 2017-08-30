# eve
## A minimalistic, flexible and powerful dependency injector

eve is a small (~200 lines of executable code), self contained **dependency injector**
choosing a **configuration approach** over reflection or meta-programming.

Dependencies are defined in code alongside their classes offering 
a high level of control over object creation, sharing and caching.

### Install

You can use *composer* to install eve.

#### Command line
```bash
$ php composer.phar install chkt/eve
```

#### composer.son
```json
{
  "require" : {
    "chkt/eve" : "<version>"
  }
}
```

Alternatively you can the *github repository* to a place of your liking.

```bash
$ git clone https://github.com/chkt/eve.git
```

### Basic usage

The injector depends on a couple of helpers to resolve and inject dependencies.
All of these are combined into the **InjectorDriver**.

```php
use \eve\driver\InjectorDriverFactory();

$factory = new InjectorDriverFactory();
$driver = $factory->produce([...]);
```

The types of injectable objects depend on the configuration of the driver.
All configuration options for creating the driver are listed in the [driver folder](./source/driver/readme.md).

Using the injector is straightforwand.

```php
$injector = $driver->getInjector();

$object = $injector->produce(\namespace\ClassName::class, [...]);
```

The optional second argument allows additional configuration options to be
passed to the injector.


Since eve is not using reflection or other meta-programming techniques,
it depends on injectable objects implementing `\eve\inject\IInjectable`,
which defines the single static method `getDependencyConfig`.

#### ClassName.php
```php

namespace namespace;

use eve\access\ITraverableAccessor;
use eve\inject\IInjectable;
use eve\inject\IInjector;



class ClassName
implements IInjectable
{

  static public function getDependencyConfig(ITraversableAccessor $config) {
    return [
      'injector:',
      'resolve:externalObject',
      [
        'type' => 'argument',
        'data' => $config->hasItem('options') ? $config->getItem('options') : []
      ]
    ];
  }


  public function __construct(IInjector $injector, ExternalObject $object) {}

}

```

The `getDependencyConfig` method of the class defines what kind of arguments new instances
of the class are to be created with.

In the example above the injector will inject itself as the first argument of the object
constructor and the object referenced by `'ExternalObject'` as the second argument.
If the injector was supplied with an array containing a property named `'options'`,
it will be supplied are the third argument, otherwise an empty array will be used.

There are a few different ways to configure dependencies built into the injector and it is easy
to configure additional ways of resolving dependencies by configuring the driver.