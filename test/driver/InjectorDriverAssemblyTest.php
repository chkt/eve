<?php

namespace test\driver;

use PHPUnit\Framework\TestCase;

use eve\common\factory\ICoreFactory;
use eve\common\factory\ISimpleFactory;
use eve\common\projection\IProjectable;
use eve\common\access\ITraversableAccessor;
use eve\common\assembly\IAssemblyHost;
use eve\common\assembly\AAssemblyHost;
use eve\common\assembly\exception\InvalidKeyException;
use eve\driver\InjectorDriverAssembly;



final class InjectorDriverAssemblyTest
extends TestCase
{

	private function _mockInterface(string $qname, array $args = []) {
		$ins = $this
			->getMockBuilder($qname)
			->getMock();

		foreach ($args as $key => & $arg) {
			$key = (is_numeric($key) ? 'p' : '') . $key;

			$ins->$key =& $arg;
		}

		return $ins;
	}

	private function _mockCoreFactory() {
		$core = $this->_mockInterface(ICoreFactory::class);

		$core
			->method('newInstance')
			->with(
				$this->isType('string'),
				$this->logicalOr(
					$this->isType('array'),
					$this->isNull()
				)
			)
			->willReturnCallback(function(string $qname, array $args = []) {
				$map = [
					\eve\common\access\TraversableMutator::class => \eve\common\access\IItemMutator::class,
					\eve\inject\IdentityInjector::class => \eve\inject\IInjector::class,
					\eve\inject\cache\KeyEncoder::class => \eve\inject\cache\IKeyEncoder::class,
					\eve\inject\resolve\ResolverAssembly::class => IAssemblyHost::class,
					\eve\entity\EntityParser::class => \eve\entity\IEntityParser::class,
					\eve\provide\ProviderAssembly::class => IAssemblyHost::class
				];

				$this->assertArrayHasKey($qname, $map);

				return $this->_mockInterface($map[$qname], $args);
			});

		return $core;
	}

	private function _mockAccessorFactory() {
		$access = $this
			->getMockBuilder(ISimpleFactory::class)
			->setMethods([ 'select' ])
			->getMockForAbstractClass();

		$access
			->method('select')
			->with(
				$this->isInstanceOf(IProjectable::class),
				$this->isType('string')
			)
			->willReturnCallback(function(IProjectable $projectable, string $key) {
				return $this->_mockInterface(ITraversableAccessor::class, [ $projectable->getProjection()[$key] ]);
			});

		return $access;
	}

	private function _mockAssembly(
		ICoreFactory $base = null,
		ISimpleFactory $access = null,
		ITraversableAccessor $config = null,
		array $methods = [ '__' ]
	) {
		if (is_null($base)) $base = $this->_mockCoreFactory();
		if (is_null($access)) $access = $this->_mockAccessorFactory();
		if (is_null($config)) $config = $this->_produceAccessor();

		$assembly = $this
			->getMockBuilder(InjectorDriverAssembly::class)
			->setMethods($methods)
			->setConstructorArgs([ $base, $access, $config ])
			->getMock();

		return $assembly;
	}

	private function _produceAccessor(array& $data = []) : ITraversableAccessor {
		return new \eve\common\access\TraversableAccessor($data);
	}


	public function testInheritance() {
		$assembly = $this->_mockAssembly();

		$this->assertInstanceOf(AAssemblyHost::class, $assembly);
	}


	public function test_produceItem() {
		$assembly = $this->_mockAssembly(null, null, null, [ '_produceFoo' ]);

		$assembly
			->method('_produceFoo')
			->willReturn('bar');

		$this->assertEquals('bar', $assembly->getItem('foo'));
	}

	public function test_produceItem_invalidKey() {
		$assembly = $this->_mockAssembly();

		$this->expectException(InvalidKeyException::class);

		$assembly->getItem('foo');
	}

	public function testGetItem_coreFactory() {
		$base = $this->_mockCoreFactory();
		$assembly = $this->_mockAssembly($base);

		$this->assertSame($base, $assembly->getItem('coreFactory'));
	}

	public function testGetItem_accessorFactory() {
		$access = $this->_mockAccessorFactory();
		$assembly = $this->_mockAssembly(null, $access);

		$this->assertSame($access, $assembly->getItem('accessorFactory'));
	}


	public function test_produceKeyEncoder() {
		$base = $this->_mockCoreFactory();
		$assembly = $this->_mockAssembly($base);
		$encoder = $assembly->getItem('keyEncoder');

		$this->assertInstanceOf(\eve\inject\cache\IKeyEncoder::class, $encoder);
		$this->assertObjectHasAttribute('p0', $encoder);
		$this->assertSame($base, $encoder->p0);
	}

	public function test_produceInstanceCache() {
		$assembly = $this->_mockAssembly();
		$cache = $assembly->getItem('instanceCache');

		$this->assertInstanceOf(\eve\common\access\IItemMutator::class, $cache);
		$this->assertObjectHasAttribute('p0', $cache);
		$this->assertInternalType('array', $cache->p0);
	}

	public function test_produceInjector() {
		$assembly = $this->_mockAssembly();
		$injector = $assembly->getItem('injector');

		$this->assertInstanceOf(\eve\inject\IInjector::class, $injector);
		$this->assertObjectHasAttribute('p0', $injector);
		$this->assertSame($assembly, $injector->p0);
	}

	public function test_produceResolverAssembly() {
		$props = [
			'resolvers' => [
				'foo' => 'bar'
			]
		];
		$config = $this->_produceAccessor($props);
		$assembly = $this->_mockAssembly(null, null, $config);
		$resolvers = $assembly->getItem('resolverAssembly');

		$this->assertInstanceOf(IAssemblyHost::class, $resolvers);
		$this->assertObjectHasAttribute('p0', $resolvers);
		$this->assertSame($assembly, $resolvers->p0);
		$this->assertObjectHasAttribute('p1', $resolvers);
		$this->assertInstanceOf(ITraversableAccessor::class, $resolvers->p1);
		$this->assertObjectHasAttribute('p0', $resolvers->p1);
		$this->assertInternalType('array', $resolvers->p1->p0);
		$this->assertEquals('bar', $resolvers->p1->p0['foo']);
	}

	public function test_produceEntityParser() {
		$assembly = $this->_mockAssembly();

		$this->assertInstanceOf(\eve\entity\IEntityParser::class, $assembly->getItem('entityParser'));
	}

	public function test_produceLocator() {
		$assembly = $this->_mockAssembly();
		$injector = $assembly->getItem('injector');

		$injector
			->method('produce')
			->with(
				$this->equalTo(\eve\provide\ProviderProvider::class),
				$this->isType('array')
			)
			->willReturnCallback(function(string $qname, array $args) {
				return $this->_mockInterface(\eve\provide\ILocator::class, $args);
			});

		$locator = $assembly->getItem('locator');

		$this->assertInstanceOf(\eve\provide\ILocator::class, $locator);
		$this->assertObjectHasAttribute('driver', $locator);
		$this->assertSame($assembly, $locator->driver);
	}

	public function test_produceProviderAssembly() {
		$props = [
			'providers' => [
				'foo' => 'bar'
			]
		];
		$config = $this->_produceAccessor($props);
		$assembly = $this->_mockAssembly(null, null, $config);
		$providers = $assembly->getItem('providerAssembly');

		$this->assertInstanceOf(IAssemblyHost::class, $providers);
		$this->assertObjectHasAttribute('p0', $providers);
		$this->assertSame($assembly, $providers->p0);
		$this->assertObjectHasAttribute('p1', $providers);
		$this->assertInstanceOf(ITraversableAccessor::class, $providers->p1);
		$this->assertObjectHasAttribute('p0', $providers->p1);
		$this->assertInternalType('array', $providers->p1->p0);
		$this->assertEquals('bar', $providers->p1->p0['foo']);
	}
}
