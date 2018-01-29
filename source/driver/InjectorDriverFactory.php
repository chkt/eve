<?php

namespace eve\driver;

use eve\common\factory\ISimpleFactory;
use eve\common\factory\ICoreFactory;
use eve\common\factory\ASimpleFactory;
use eve\common\access\ITraversableAccessor;
use eve\common\access\IItemMutator;
use eve\entity\IEntityParser;
use eve\inject\IInjector;
use eve\inject\cache\IKeyEncoder;
use eve\provide\ILocator;



class InjectorDriverFactory
extends ASimpleFactory
{

	protected function _getConfigDefaults() : array {
		return [
			'accessorFactoryName' => \eve\common\access\TraversableAccessorFactory::class,
			'driverName' => \eve\driver\InjectorDriver::class,
			'entityParserName' => \eve\entity\EntityParser::class,
			'injectorName' => \eve\inject\IdentityInjector::class,
			'locatorName' => \eve\provide\ProviderProvider::class,
			'resolvers' => [
				IInjector::TYPE_INJECTOR => \eve\inject\resolve\HostResolver::class,
				IInjector::TYPE_LOCATOR => \eve\inject\resolve\HostResolver::class,
				IInjector::TYPE_ARGUMENT => \eve\inject\resolve\ArgumentResolver::class,
				IInjector::TYPE_FACTORY => \eve\inject\resolve\FactoryResolver::class
			],
			'providers' => [],
			'keyEncoderName' => \eve\inject\cache\KeyEncoder::class,
			'instanceCacheName' => \eve\common\access\TraversableMutator::class
		];
	}


	protected function _produceAccessorFactory(ICoreFactory $core, array $config) : ISimpleFactory {
		return array_key_exists('accessorFactory', $config) ?
			$config['accessorFactory'] :
			$core->newInstance($config['accessorFactoryName'], [ $core ]);
	}


	protected function _produceDriver(ICoreFactory $core, ITraversableAccessor $config, array& $dependencies) : IInjectorDriver {
		return $core->newInstance($config->getItem('driverName'), [ & $dependencies ]);
	}


	protected function _produceKeyEncoder(IInjectorDriver $driver, ITraversableAccessor $config) : IKeyEncoder {
		return $config->hasKey('keyEncoder') ?
			$config->getItem('keyEncoder') :
			$driver
			->getCoreFactory()
			->newInstance($config->getItem('keyEncoderName'), [ $driver->getCoreFactory() ]);
	}

	protected function _produceInstanceCache(IInjectorDriver $driver, ITraversableAccessor $config) : IItemMutator {
		return $config->hasKey('instanceCache') ?
			$config->getItem('instanceCache') :
			$driver
				->getCoreFactory()
				->newInstance($config->getItem('instanceCacheName'), [ [] ]);
	}

	protected function _produceEntityParser(IInjectorDriver $driver, ITraversableAccessor $config) : IEntityParser {
		return $config->hasKey('entityParser') ?
			$config->getItem('entityParser') :
			$driver
				->getCoreFactory()
				->newInstance($config->getItem('entityParserName'));
	}

	protected function _produceInjector(IInjectorDriver $driver, ITraversableAccessor $config) : IInjector {
		return $config->hasKey('injector') ?
			$config->getItem('injector') :
			$driver
				->getCoreFactory()
				->newInstance($config->getItem('injectorName'), [
					$driver,
					$config->getItem('resolvers')
				]);
	}

	protected function _produceLocator(IInjectorDriver $driver, ITraversableAccessor $config) : ILocator {
		return $config->hasKey('locator') ?
			$config->getItem('locator') :
			$driver
				->getInjector()
				->produce($config->getItem('locatorName'), [
					'driver' => $driver,
					'providerNames' => $config->getItem('providers')
				]);
	}


	protected function _produceInstance(ICoreFactory $core, array $config) {
		$access = $this->_produceAccessorFactory($core, $config);

		$deps = [
			IInjectorDriver::ITEM_CORE_FACTORY => $core,
			IInjectorDriver::ITEM_ACCESSOR_FACTORY => $access
		];

		$data = $access->produce($config);
		$driver = $this->_produceDriver($core, $data, $deps);

		$deps[IInjectorDriver::ITEM_KEY_ENCODER] = $this->_produceKeyEncoder($driver, $data);
		$deps[IInjectorDriver::ITEM_INSTANCE_CACHE] = $this->_produceInstanceCache($driver, $data);
		$deps[IInjectorDriver::ITEM_ENTITY_PARSER] = $this->_produceEntityParser($driver, $data);
		$deps[IInjectorDriver::ITEM_INJECTOR] = $this->_produceInjector($driver, $data);
		$deps[IInjectorDriver::ITEM_LOCATOR] = $this->_produceLocator($driver, $data);

		return $driver;
	}
}
