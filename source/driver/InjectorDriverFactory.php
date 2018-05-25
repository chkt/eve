<?php

namespace eve\driver;

use eve\common\factory\IBaseFactory;
use eve\common\factory\ISimpleFactory;
use eve\common\factory\ASimpleFactory;
use eve\common\access\ITraversableAccessor;
use eve\common\assembly\IAssemblyHost;
use eve\inject\IInjector;



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


	protected function _produceAccessorFactory(IBaseFactory $base, array $config) : ISimpleFactory {
		return $base->newInstance(\eve\common\access\factory\TraversableAccessorFactory::class, [ $base ]);
	}

	protected function _produceAssembly(IBaseFactory $base, ISimpleFactory $access, ITraversableAccessor $config) : IAssemblyHost {
		return $base->newInstance(\eve\driver\InjectorDriverAssembly::class, [
			$base,
			$access,
			$config
		]);
	}

	protected function _produceDriver(IAssemblyHost $assembly) : IInjectorDriver {
		return $assembly
			->getItem('baseFactory')
			->newInstance(\eve\driver\InjectorDriver::class, [
				$assembly
			]);
	}


	protected function _produceInstance(IBaseFactory $base, array $config) {
		$access = $this->_produceAccessorFactory($base, $config);
		$data = $access->produce($config);

		$assembly = $this->_produceAssembly($base, $access, $data);

		return $this->_produceDriver($assembly);
	}
}
