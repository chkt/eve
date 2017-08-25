<?php

namespace test\inject;

use eve\access\TraversableAccessor;
use PHPUnit\Framework\TestCase;

use eve\common\ISimpleFactory;
use eve\factory\ICoreFactory;
use eve\access\ITraversableAccessor;
use eve\entity\IEntityParser;
use eve\entity\EntityParser;
use eve\driver\IInjectorDriver;
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
			->setMethods([ 'construct', 'getDependencyConfigMember', 'produce' ])
			->getMockForAbstractClass();

		$ins
			->expects($this->once())
			->method('construct')
			->with()
			->willReturn($ins);

		$ins
			->expects($this->once())
			->method('getDependencyConfigMember')
			->with($this->isInstanceOf(ITraversableAccessor::class))
			->willReturn([]);

		$ins
			->expects($this->once())
			->method('produce')
			->with($this->isInstanceOf(ITraversableAccessor::class))
			->willReturnCallback(function(ITraversableAccessor $config) {
				return $config->getProjection();
			});

		return $ins;
	}


	private function _mockFactory(array $map = []) : ICoreFactory {
		$ins = $this
			->getMockBuilder(ICoreFactory::class)
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

	private function _mockDriver(ICoreFactory $factory = null, IEntityParser $parser = null, ISimpleFactory $accessor = null) : IInjectorDriver {
		if(is_null($factory)) $factory = $this->_mockFactory();
		if (is_null($parser)) $parser = $this->_produceParser();
		if (is_null($accessor)) $accessor = $this->_mockAccessorFactory();

		$ins = $this
			->getMockBuilder(IInjectorDriver::class)
			->getMock();

		$ins
			->expects($this->once())
			->method('getFactory')
			->with()
			->willReturn($factory);

		$ins
			->expects($this->once())
			->method('getEntityParser')
			->with()
			->willReturn($parser);

		$ins
			->expects($this->once())
			->method('getAccessorFactory')
			->with()
			->willReturn($accessor);

		return $ins;
	}


	private function _produceParser() : IEntityParser {
		return new EntityParser();
	}

	private function _produceInjector(IInjectorDriver $driver = null, array $resolverNames = []) {
		if (is_null($driver)) $driver = $this->_mockDriver();

		return new Injector($driver, $resolverNames);
	}


	public function testInheritance() {
		$injector = $this->_produceInjector();

		$this->assertInstanceOf(IInjector::class, $injector);
	}

	public function testProduce_noDeps() {
		$factory = $this->_mockFactory([
			'foo' => $this->_mockInjectable()
		]);
		$driver = $this->_mockDriver($factory);
		$injector = $this->_produceInjector($driver);

		$injectable = $injector->produce('foo', []);

		$this->assertInstanceOf(IInjectable::class, $injectable);
		$this->assertEmpty($injectable->getArgs());
	}

	public function testProduce() {
		$factory = $this->_mockFactory([
			'injectable' => $this->_mockInjectable(),
			'resolver' => $this->_mockResolver()
		]);
		$driver = $this->_mockDriver($factory);
		$injector = $this->_produceInjector($driver, [
			'foo' => 'resolver'
		]);

		$injectable = $injector->produce('injectable', [[
			'type' => 'foo',
		]]);

		$this->assertEquals([[ 'type' => 'foo' ]], $injectable->getArgs());
	}

	public function testProduce_entity() {
		$factory = $this->_mockFactory([
			'injectable' => $this->_mockInjectable(),
			'resolver' => $this->_mockResolver()
		]);
		$driver = $this->_mockDriver($factory);
		$injector = $this->_produceInjector($driver, [
			'foo' => 'resolver'
		]);

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
		$driver = $this->_mockDriver($factory);
		$injector = $this->_produceInjector($driver);

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('INJ invalid dependency');

		$injector->produce('injectable', [ 1 ]);
	}

	public function testProduce_malformedDependency() {
		$factory = $this->_mockFactory([
			'injectable' => $this->_mockInjectable()
		]);
		$driver = $this->_mockDriver($factory);
		$injector = $this->_produceInjector($driver);

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('INJ malformed dependency');

		$injector->produce('injectable', [[]]);
	}

	public function testProduce_unknownDependency() {
		$factory = $this->_mockFactory([
			'injectable' => $this->_mockInjectable()
		]);
		$driver = $this->_mockDriver($factory);
		$injector = $this->_produceInjector($driver);

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('INJ unknown dependency "foo"');

		$injector->produce('injectable', [[
			'type' => 'foo'
		]]);
	}
}
