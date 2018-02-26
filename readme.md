# eve
## A minimalistic, flexible and powerful dependency injector

eve is a small (~250 lines of executable code), self contained *dependency injector*
choosing a *configuration approach* over reflection, annotations or similar
meta-programming techniques.

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

Alternatively you can clone the *github repository* to a place of your liking.

```bash
$ git clone https://github.com/chkt/eve.git
```

### Basic usage

The injector depends on a couple of helpers to resolve and inject dependencies.
All of these are combined into the `InjectorDriver`.

```php
use \eve\common\factory\CoreFactory;
use \eve\driver\InjectorDriverFactory;

$core = new CoreFactory();
$factory = $core->newInstance(InjectorDriverFactory::class, [ $core ]);
$driver = $factory->produce([...]);
```

The *first* line creates the `CoreFactory`, which supplies the basic means of instantiating objects.
In the *second* line the CoreFactory is used to create the `InjectorDriverFactory`,
which in line *three* creates the `InjectorDriver`.

The types of injectable objects depend on the configuration of the driver.
The options for configuring the driver are listed in the [driver folder](./source/driver/readme.md).

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

```php
use eve\access\ITraverableAccessor;
use eve\inject\IInjectable;
use eve\inject\IInjector;

class ExampleClass
implements IInjectable
{
  
  static public function getDependencyConfig(ITraversableAccessor $config) {
    return [
      'injector:',
      'providerName:providableObject?key=value',
      [
        'type' => 'argument',
        'data' => $config->hasItem('options') ? $config->getItem('options') : []
      ]
    ];
  }
  
  public function __construct(IInjector $injector, ProvidableObject $object, array $options) {
  
  }
}
```

The `getDependencyConfig` method defines what kind of arguments new instances
of the class are to be created with.

Dependencies can either be *arrays* with a `'type'` and one or more additional keys, or be
defined using *entity* syntax, referring to dependencies through url formatted *strings*.

In the example above the injector will inject itself as the first constructor argument.
The second argument is an object registered as `'providableObject'` at the provider named `'providerName'`
and configured with `key=value`. 

If the injector was supplied with an array containing a property named `'options'`,
as its second argument, it is supplied as the third argument.

```php
$injector->produce(\exampleNamespace\ExampleClass::class, [ 'options' => [...] ]);
```

If no second argument was supplied to the injector or it did not contain an `'options'` property,
`getDependencyConfig` returns an empty array as the third argument.

```php
$injector->produce(\exampleNamespace\ExampleClass::class);
```
