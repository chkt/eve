<?php

namespace eve\driver;

use eve\common\ISimpleFactory;
use eve\inject\IInjector;



function array_merge_deep(array $a, array $b) {
	foreach ($b as $key => $value) {
		if (
			!array_key_exists($key, $a) ||
			!is_array($a[$key]) ||
			!is_array($value)
		) $a[$key] = $value;
		else $a[$key] = array_merge_deep($a[$key], $value);
	}

	return $a;
}



final class InjectorDriverFactory
implements ISimpleFactory
{

	public function produce(array& $config = []) {
		$spec = array_merge_deep([
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
		], $config);

		$fab = array_key_exists('coreFactory', $spec) ?
			$spec['coreFactory'] :
			new $spec['coreFactoryName']();

		$access = array_key_exists('accessorFactory', $spec) ?
			$spec['accessorFactory'] :
			$fab->newInstance($spec['accessorFactoryName'], [ $fab ]);

		$data = $access->produce($spec);

		$deps = [
			IInjectorDriver::ITEM_CORE_FACTORY => $fab,
			IInjectorDriver::ITEM_ACCESSOR_FACTORY => $access
		];

		$driver = $fab->newInstance(InjectorDriver::class, [ & $deps ]);

		$refs = $data->getItem('references');
		$deps[IInjectorDriver::ITEM_REFERENCES] = $access->produce($refs);

		$deps[IInjectorDriver::ITEM_INSTANCE_CACHE] =  $data->hasKey('instanceCache') ?
			$data->getItem('instanceCache') :
			$fab->newInstance($data->getItem('instanceCacheName'), [ [] ]);

		$deps[IInjectorDriver::ITEM_ENTITY_PARSER] = $data->hasKey('entityParser') ?
			$data->getItem('entityParser') :
			$fab->newInstance($data->getItem('entityParserName'));

		$deps[IInjectorDriver::ITEM_INJECTOR] = $data->hasKey('injector') ?
			$data->getItem('injector') :
			$fab->newInstance($data->getItem('injectorName'), [
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
