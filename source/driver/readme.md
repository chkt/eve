# InjectorDriver
## Creation

Creating a basic driver is easy.

```php
use eve\common\factory\BaseFactory;
use eve\driver\InjectorDriverFactory;

$base = new BaseFactory();
$factory = $base->produce(InjectorDriverFactory::class, [ $base ]);
$driver = $factory->produce();
```

The basic driver supplied by eve allows for the configuration of resolvers and providers.
Additionally the driver can easily extended to augment or change its operation.

The example below shows all default config options and their respective values:

```php
$driver = $factory->produce([
  'resolvers' => [
    'injector' => \eve\inject\resolve\HostResolver::class,
    'locator' => \eve\inject\resolve\HostResolver::class,
    'argument' => \eve\inject\resolve\ArgumentResolver::class,
    'factory' => \eve\inject\resolve\FactoryResolver::class
  ],
  'providers' => []
]);
```

### Options

#### `'resolvers'`
An array of qualified names of classes implementing `\eve\inject\resolve\IInjectorResolver` -
The dependency resolvers used by the injector.

```php
'resolvers' => [
  'newResolver' => \exampleNamespace\NewResolver::class
]
```

Injectable classes can address added resolvers by their respective configuration names

```php
  
  use \eve\inject\IInjectable;
  use \eve\common\access\ITraversableAccessor; 
  
  class ExampleInjectable
  implements IInjectable
  {
  
    static public function getDependencyConfig(ITraversableAccessor $config) : array {
      return [ 'newResolver:exampleResolvable'];
    }
    
    public function __construct(ExampleResolveable $example) {
      
    }
  }
```

#### `'providers'`
An array of qualified name strings *or* arrays with `'qname'` and optional `'config'` keys.
Each entry represents a class implementing `\eve\provide\IProvider` - The providers
used by the locator. Using the array syntax allows providers to be configured before use.


```php
'providers' => [
  'stringProvider' => \exampleNamespace\StringProvider::class,
  'arrayProvider' => [
    'qname' => \exampleNamespace\ArrayProvider::class,
    'config' => [ ... ]
  ]
]
```

Consumers of the Locator can address the added providers by using their respective configuration
names.

```php

use \eve\injector\IInjectable;
use \eve\common\access\ITraversableAccessor;
use \eve\provide\ILocator;

class ExampleClass
implements IInjectable
{
  
  static public function getDependencyConfig(ITraversableAccessor $config) : array {
    return [ 'locator:' ];
  }
  
  public function __construct(ILocator $locator) {
    $example = $locator->locate('stringProvider:exampleProvidable'):
  }
}
```

Eve does not configure any providers itself, but by extending `\eve\provide\AProvider`,
client code can very easily create its own providers.
