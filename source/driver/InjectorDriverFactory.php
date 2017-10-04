<?php

namespace eve\driver;

use eve\common\factory\ASimpleFactory;
use eve\inject\IInjector;



final class InjectorDriverFactory
extends ASimpleFactory
{

	protected function _getConfigDefaults() : array {
		return [
			'coreFactoryName' => \eve\factory\CoreFactory::class,
			'accessorFactoryName' => \eve\access\TraversableAccessorFactory::class,
			'entityParserName' => \eve\entity\EntityParser::class,
			'injectorName' => \eve\inject\IdentityInjector::class,
			'locatorName' => \eve\provide\ProviderProvider::class,
			'resolvers' => [
				IInjector::TYPE_INJECTOR => \eve\inject\resolve\HostResolver::class,
				IInjector::TYPE_LOCATOR => \eve\inject\resolve\HostResolver::class,
				IInjector::TYPE_RESOLVE => \eve\inject\resolve\ReferenceResolver::class,
				IInjector::TYPE_ARGUMENT => \eve\inject\resolve\ArgumentResolver::class,
				IInjector::TYPE_FACTORY => \eve\inject\resolve\FactoryResolver::class
			],
			'providers' => [],
			'references' => [],
			'instanceCacheName' => \eve\access\TraversableMutator::class
		];
	}


	protected function _produceInstance(array $config) {
		$core = array_key_exists('coreFactory', $config) ?
			$config['coreFactory'] :
			new $config['coreFactoryName']();

		$access = array_key_exists('accessorFactory', $config) ?
			$config['accessorFactory'] :
			$core->newInstance($config['accessorFactoryName'], [ $core ]);

		$data = $access->produce($config);

		$deps = [
			IInjectorDriver::ITEM_CORE_FACTORY => $core,
			IInjectorDriver::ITEM_ACCESSOR_FACTORY => $access
		];

		$driver = $core->newInstance(InjectorDriver::class, [ & $deps ]);

		$refs = $data->getItem('references');
		$deps[IInjectorDriver::ITEM_REFERENCES] = $access->produce($refs);

		$deps[IInjectorDriver::ITEM_INSTANCE_CACHE] =  $data->hasKey('instanceCache') ?
			$data->getItem('instanceCache') :
			$core->newInstance($data->getItem('instanceCacheName'), [ [] ]);

		$deps[IInjectorDriver::ITEM_ENTITY_PARSER] = $data->hasKey('entityParser') ?
			$data->getItem('entityParser') :
			$core->newInstance($data->getItem('entityParserName'));

		$deps[IInjectorDriver::ITEM_INJECTOR] = $data->hasKey('injector') ?
			$data->getItem('injector') :
			$core->newInstance($data->getItem('injectorName'), [
				$driver,
				$data->getItem('resolvers')
			]);

		$deps[IInjectorDriver::ITEM_LOCATOR] = $data->hasKey('locator') ?
			$data->getItem('locator') :
			$deps['injector']->produce($data->getItem('locatorName'), [
				'driver' => $driver,
				'providerNames' => $data->getItem('providers')
			]);

		return $driver;
	}
}
