# InjectorDriver
## Creation

Creating a basic driver is easy:

```php
use eve\driver\InjectorDriverFactory;

$factory = new InjectorDriverFactory();
$driver = $factory->produce();
```

Driver creation is highly configurable. The example below shows all config options with
their respective defaults:

```php
$driver = $factory->produce([
  'coreFactoryName' => \eve\factory\CoreFactory::class,
  'accessorFactoryName' => \eve\access\TraversableAccessorFactory::class,
  'entityParserName' => \eve\entity\EntityParser::class,
  'injectorName' => \eve\inject\IdentityInjector::class,
  'locatorName' => \eve\provide\ProviderProvider::class,
  'resolvers' => [
    'injector' => \eve\inject\resolve\HostResolver::class,
    'locator' => \eve\inject\resolve\HostResolver::class,
    'resolve' => \eve\inject\resolve\ReferenceResolver::class,
    'argument' => \eve\inject\resolve\ArgumentResolver::class,
    'factory' => \eve\inject\resolve\FactoryResolver::class
  ],
  'providers' => [],
  'references' => [],
  'instanceCache' => \eve\access\TraversableMutator::class
]);
```

### Options

* `'coreFactoryName'`:
the qualified name of a class implementing `\eve\factory\ICoreFactory` -
The factory creating all objects
* `'accessorFactoryName'`:
the qualified name of a class implementing `\eve\common\ISimpleFactory`
*and* supplying objects implementing `\eve\access\ITraversableAccessor` -
The factory creating the accessor objects used in configuring dependencies
* `'entityParserName'`:
The qualified name of a class implementing `\eve\entity\IEntityParser` -
The object responsible for resolving string entity type dependencies
* `'injectorName'`:
The qualified name of a class implementing `\eve\inject\IInjector` -
The injector. Besides `\eve\inject\IdentityInjector` eve also comes with
`\eve\inject\Injector` (which will not provide an object cache)
* `'locatorName'`:
The qualified name of a class implementing `\eve\provide\ILocator` -
The locator.
* `'resolvers'`:
An array of qualified names of classes implementing `\eve\inject\resolve\IInjectorResolver` -
The dependency resolver names used by the injector.
* `'providers'`:
An array of qualified names of classes implementing `\eve\provide\IProvider` -
The provider names used by the locator. eve does not configure any providers on its own, but by using
`\eve\provide\AProvider` client code can very easily create its own conforming providers.
* `'references'`:
An array of predefined objects to be made available to the injector.
* `'instanceCache'`:
The qualified name of a class implementing `\eve\access\IItemMutator` -
The object cache/dependency container.
 


An alternative syntax exists for supplying pre-created objects for one or more dependencies of the driver:

```php
$driver = $factory->produce([
  'coreFactory' => $coreFactory,
  'accessorFactory' => $accessorFactory,
  'entityParser' => $entityParser,
  'injector' => $injector,
  'locator' => $locator,
  'instanceCache' => $instanceCache
]);
```
