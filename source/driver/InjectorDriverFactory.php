<?php

namespace eve\driver;

use eve\common\factory\ISimpleFactory;
use eve\common\factory\ICoreFactory;
use eve\common\factory\ASimpleFactory;
use eve\common\access\ITraversableAccessor;
use eve\common\access\IItemMutator;
use eve\common\access\TraversableMutator;
use eve\common\access\TraversableAccessorFactory;
use eve\entity\IEntityParser;
use eve\entity\EntityParser;
use eve\inject\IInjector;
use eve\inject\IdentityInjector;
use eve\inject\cache\IKeyEncoder;
use eve\inject\cache\KeyEncoder;
use eve\provide\ILocator;
use eve\provide\ProviderProvider;



class InjectorDriverFactory
extends ASimpleFactory
{

	protected function _getConfigDefaults() : array {
		return [
			'resolvers' => [
				IInjector::TYPE_INJECTOR => \eve\inject\resolve\HostResolver::class,
				IInjector::TYPE_LOCATOR => \eve\inject\resolve\HostResolver::class,
				IInjector::TYPE_ARGUMENT => \eve\inject\resolve\ArgumentResolver::class,
				IInjector::TYPE_FACTORY => \eve\inject\resolve\FactoryResolver::class
			],
			'providers' => []
		];
	}


	protected function _produceAccessorFactory(ICoreFactory $base, array $config) : ISimpleFactory {
		return $base->newInstance(TraversableAccessorFactory::class);
	}

	protected function _produceDriver(ICoreFactory $base, ITraversableAccessor $config, array& $dependencies) : IInjectorDriver {
		return $base->newInstance(InjectorDriver::class, [ & $dependencies ]);
	}

	protected function _produceKeyEncoder(IInjectorDriver $driver, ITraversableAccessor $config) : IKeyEncoder {
		$base = $driver->getCoreFactory();

		return $base->newInstance(KeyEncoder::class, [ $base ]);
	}

	protected function _produceInstanceCache(IInjectorDriver $driver, ITraversableAccessor $config) : IItemMutator {
		return $driver->getCoreFactory()->newInstance(TraversableMutator::class, [ [] ]);
	}

	protected function _produceEntityParser(IInjectorDriver $driver, ITraversableAccessor $config) : IEntityParser {
		return $driver->getCoreFactory()->newInstance(EntityParser::class);
	}

	protected function _produceInjector(IInjectorDriver $driver, ITraversableAccessor $config) : IInjector {
		return $driver->getCoreFactory()->newInstance(IdentityInjector::class, [
			$driver,
			$config->getItem('resolvers')
		]);
	}

	protected function _produceLocator(IInjectorDriver $driver, ITraversableAccessor $config) : ILocator {
		return $driver->getInjector()->produce(ProviderProvider::class, [
			'driver' => $driver,
			'providerNames' => $config->getItem('providers')
		]);
	}


	protected function _produceInstance(ICoreFactory $base, array $config) {
		$access = $this->_produceAccessorFactory($base, $config);

		$deps = [
			IInjectorDriver::ITEM_CORE_FACTORY => $base,
			IInjectorDriver::ITEM_ACCESSOR_FACTORY => $access
		];

		$data = $access->produce($config);
		$driver = $this->_produceDriver($base, $data, $deps);

		$deps[IInjectorDriver::ITEM_KEY_ENCODER] = $this->_produceKeyEncoder($driver, $data);
		$deps[IInjectorDriver::ITEM_INSTANCE_CACHE] = $this->_produceInstanceCache($driver, $data);
		$deps[IInjectorDriver::ITEM_ENTITY_PARSER] = $this->_produceEntityParser($driver, $data);
		$deps[IInjectorDriver::ITEM_INJECTOR] = $this->_produceInjector($driver, $data);
		$deps[IInjectorDriver::ITEM_LOCATOR] = $this->_produceLocator($driver, $data);

		return $driver;
	}
}
