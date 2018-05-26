<?php

namespace test\inject;

use PHPUnit\Framework\TestCase;

use eve\common\factory\ISimpleFactory;
use eve\common\factory\IBaseFactory;
use eve\common\access\ITraversableAccessor;
use eve\common\access\TraversableAccessor;
use eve\common\assembly\IAssemblyHost;
use eve\common\assembly\exception\InvalidKeyException;
use eve\entity\IEntityParser;
use eve\entity\EntityParser;
use eve\inject\IInjector;
use eve\inject\IInjectable;
use eve\inject\Injector;
use eve\inject\resolve\IInjectorResolver;



final class InjectorTest
extends TestCase
{

	private function _mockInjectable() : IInjectable {
		$items = null;
		$ins = $this
			->getMockBuilder(IInjectable::class)
			->setMethods([ 'getDependencyConfigMember', 'construct', 'getArgs' ])
			->getMockForAbstractClass();

		$ins
			->expects($this->once())
			->method('getDependencyConfigMember')
			->with($this->isInstanceOf(ITraversableAccessor::class))
			->willReturnCallback(function (ITraversableAccessor $config) {
				return $config->getProjection();
			});

		$ins
			->expects($this->any())
			->method('construct')
			->with()
			->willReturnCallback(function(array ...$args) use (& $items, $ins) {
				$items = $args;

				return $ins;
			});

		$ins
			->expects($this->any())
			->method('getArgs')
			->with()
			->willReturnReference($items);

		return $ins;
	}

	private function _mockResolver() : IInjectorResolver {
		$ins = $this
			->getMockBuilder(IInjectorResolver::class)
			->setMethods([ 'produce' ])
			->getMockForAbstractClass();

		$ins
			->expects($this->once())
			->method('produce')
			->with($this->isInstanceOf(ITraversableAccessor::class))
			->willReturnCallback(function(ITraversableAccessor $config) {
				return $config->getProjection();
			});

		return $ins;
	}

	private function _mockResolvers(array $map = []) {
		$assembly = $this
			->getMockBuilder(IAssemblyHost::class)
			->getMock();

		$assembly
			->method('getItem')
			->with($this->isType('string'))
			->willReturnCallback(function(string $key) use ($map) {
				if (!array_key_exists($key, $map)) throw new InvalidKeyException($key);

				return $map[$key];
			});

		return $assembly;
	}


	private function _mockFactory(array $map = []) : IBaseFactory {
		$ins = $this
			->getMockBuilder(IBaseFactory::class)
			->getMock();

		$ins
			->expects($this->any())
			->method('hasInterface')
			->with($this->isType('string'), $this->isType('string'))
			->willReturnCallback(function(string $qname, string $iname) use ($map) {
				return array_key_exists($qname, $map);
			});

		$ins
			->expects($this->any())
			->method('callMethod')
			->with($this->isType('string'), $this->isType('string'), $this->isType('array'))
			->willReturnCallback(function(string $qname, string $method, array $args) use ($map) {
				if ($method === 'getDependencyConfig') $method = 'getDependencyConfigMember';

				return $map[$qname]->$method(...$args);
			});

		$ins
			->expects($this->any())
			->method('newInstance')
			->with($this->isType('string'), $this->isType('array'))
			->willReturnCallback(function(string $qname, array $args) use ($map) {
				return $map[$qname]->construct(...$args);
			});

		return $ins;
	}

	private function _mockAccessorFactory() : ISimpleFactory {
		$ins = $this
			->getMockBuilder(ISimpleFactory::class)
			->getMock();

		$ins
			->expects($this->any())
			->method('produce')
			->with($this->isType('array'))
			->willReturnCallback(function(array& $data) {
				return new TraversableAccessor($data);
			});

		return $ins;
	}

	private function _mockDriverAssembly(
		IBaseFactory $factory = null,
		IEntityParser $parser = null,
		ISimpleFactory $accessor = null,
		IAssemblyHost $resolvers = null
	) : IAssemblyHost {
		if(is_null($factory)) $factory = $this->_mockFactory();
		if (is_null($parser)) $parser = $this->_produceParser();
		if (is_null($accessor)) $accessor = $this->_mockAccessorFactory();
		if (is_null($resolvers)) $resolvers = $this->_mockResolvers();

		$ins = $this
			->getMockBuilder(IAssemblyHost::class)
			->getMock();

		$ins
			->method('getItem')
			->with($this->isType('string'))
			->willReturnCallback(function(string $key) use ($factory, $parser, $accessor, $resolvers) {
				if ($key === 'baseFactory') return $factory;
				else if ($key === 'accessorFactory') return $accessor;
				else if ($key === 'entityParser') return $parser;
				else if ($key === 'resolverAssembly') return $resolvers;
				else $this->fail($key);
			});

		return $ins;
	}


	private function _produceParser() : IEntityParser {
		return new EntityParser();
	}

	private function _produceInjector(IAssemblyHost $driverAssembly = null) {
		if (is_null($driverAssembly)) $driverAssembly = $this->_mockDriverAssembly();

		return new Injector($driverAssembly);
	}


	public function testInheritance() {
		$injector = $this->_produceInjector();

		$this->assertInstanceOf(IInjector::class, $injector);
		$this->assertInstanceOf(\eve\common\factory\IInstancingFactory::class, $injector);
		$this->assertInstanceOf(\eve\common\IFactory::class, $injector);
	}


	public function testProduce_noDependencies() {
		$factory = $this->_mockFactory([
			'foo' => $this->_mockInjectable()
		]);

		$driver = $this->_mockDriverAssembly($factory);
		$injector = $this->_produceInjector($driver);

		$injectable = $injector->produce('foo', []);

		$this->assertInstanceOf(IInjectable::class, $injectable);
		$this->assertEmpty($injectable->getArgs());
	}

	public function testProduce() {
		$resolvers = $this->_mockResolvers([ 'foo' => $this->_mockResolver() ]);
		$factory = $this->_mockFactory([ 'injectable' => $this->_mockInjectable() ]);

		$driver = $this->_mockDriverAssembly($factory, null, null, $resolvers);
		$injector = $this->_produceInjector($driver);

		$injectable = $injector->produce('injectable', [[
			'type' => 'foo',
		]]);

		$this->assertEquals([[ 'type' => 'foo' ]], $injectable->getArgs());
	}

	public function testProduce_entity() {
		$resolvers = $this->_mockResolvers([ 'foo' => $this->_mockResolver() ]);
		$factory = $this->_mockFactory([ 'injectable' => $this->_mockInjectable() ]);

		$driver = $this->_mockDriverAssembly($factory, null, null, $resolvers);
		$injector = $this->_produceInjector($driver);

		$injectable = $injector->produce('injectable', [
			'foo:'
		]);

		$this->assertEquals([
			[ 'type' => 'foo', 'location' => '' ]
		], $injectable->getArgs());
	}

	public function testProduce_notInjectable() {
		$injector = $this->_produceInjector();

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('INJ not injectable "foo"');

		$injector->produce('foo');
	}

	public function testProduce_invalidDependency() {
		$factory = $this->_mockFactory([
			'injectable' => $this->_mockInjectable()
		]);
		$driver = $this->_mockDriverAssembly($factory);
		$injector = $this->_produceInjector($driver);

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('INJ invalid dependency');

		$injector->produce('injectable', [ 1 ]);
	}

	public function testProduce_malformedDependency() {
		$factory = $this->_mockFactory([
			'injectable' => $this->_mockInjectable()
		]);
		$driver = $this->_mockDriverAssembly($factory);
		$injector = $this->_produceInjector($driver);

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('INJ malformed dependency');

		$injector->produce('injectable', [[]]);
	}

	public function testProduce_unknownDependency() {
		$factory = $this->_mockFactory([
			'injectable' => $this->_mockInjectable()
		]);
		$driver = $this->_mockDriverAssembly($factory);
		$injector = $this->_produceInjector($driver);

		$this->expectException(InvalidKeyException::class);
		$this->expectExceptionMessage('ASM invalid key "foo"');

		$injector->produce('injectable', [[
			'type' => 'foo'
		]]);
	}
}
