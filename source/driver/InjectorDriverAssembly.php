<?php

namespace eve\driver;

use eve\common\factory\ICoreFactory;
use eve\common\factory\ISimpleFactory;
use eve\common\access\IItemAccessor;
use eve\common\access\ITraversableAccessor;
use eve\common\access\IItemMutator;
use eve\common\assembly\IAssemblyHost;
use eve\common\assembly\AAssemblyHost;
use eve\inject\IInjector;
use eve\inject\cache\IKeyEncoder;
use eve\provide\ILocator;
use eve\entity\IEntityParser;



class InjectorDriverAssembly
extends AAssemblyHost
{

	private $_config;


	public function __construct(
		ICoreFactory $baseFactory,
		ISimpleFactory $accessorFactory,
		ITraversableAccessor $config
	) {
		$data = [
			'coreFactory' => $baseFactory,
			'accessorFactory' => $accessorFactory
		];

		parent::__construct($data);

		$this->_config = $config;
	}


	protected function _produceKeyEncoder(ITraversableAccessor $config) : IKeyEncoder {
		$base = $this->getItem('coreFactory');

		return $base->newInstance(\eve\inject\cache\KeyEncoder::class, [ $base ]);
	}

	protected function _produceInstanceCache(ITraversableAccessor $config) : IItemMutator {
		return $this
			->getItem('coreFactory')
			->newInstance(\eve\common\access\TraversableMutator::class, [ [] ]);
	}

	protected function _produceInjector(ITraversableAccessor $config) : IInjector {
		return $this
			->getItem('coreFactory')
			->newInstance(\eve\inject\IdentityInjector::class, [ $this ]);
	}

	protected function _produceResolverAssembly(ITraversableAccessor $config) : IAssemblyHost {
		return $this
			->getItem('coreFactory')
			->newInstance(\eve\inject\resolve\ResolverAssembly::class, [
				$this,
				$this
					->getItem('accessorFactory')
					->select($config, 'resolvers')
			]);
	}

	protected function _produceEntityParser(ITraversableAccessor $config) : IEntityParser {
		return $this
			->getItem('coreFactory')
			->newInstance(\eve\entity\EntityParser::class);
	}

	protected function _produceLocator(ITraversableAccessor $config) : ILocator {
		return $this
			->getItem('injector')
			->produce(\eve\provide\ProviderProvider::class, [
				'driver' => $this
			]);
	}

	protected function _produceProviderAssembly(ITraversableAccessor $config) : IItemAccessor {
		return $this
			->getItem('coreFactory')
			->newInstance(\eve\provide\ProviderAssembly::class, [
				$this,
				$this
					->getItem('accessorFactory')
					->select($config, 'providers')
			]);
	}


	protected function _produceItem(string $key) {
		$method = '_produce' . ucfirst($key);

		if (!method_exists($this, $method)) throw new \ErrorException(sprintf('FAC invalid key "%s"', $key));

		return $this->$method($this->_config);
	}
}
