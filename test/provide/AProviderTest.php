<?php

namespace test\provide;

use PHPUnit\Framework\TestCase;

use eve\common\factory\IBaseFactory;
use eve\common\access\IItemAccessor;
use eve\common\access\TraversableAccessor;
use eve\common\assembly\IAssemblyHost;
use eve\inject\IInjectable;
use eve\inject\IInjector;
use eve\provide\IProvider;
use eve\provide\AProvider;



class AProviderTest
extends TestCase
{

	private function _mockInjector(callable $fn = null) : IInjector {
		$ins = $this
			->getMockBuilder(IInjector::class)
			->getMock();

		$ins
			->expects($this->any())
			->method('produce')
			->with(
				$this->isType('string'),
				$this->isType('array')
			)
			->willReturnCallback(function(string $qname, array $config) {
				return [
					'qname' => $qname,
					'config' => $config
				];
			});

		return $ins;
	}

	private function _mockFactory(callable $fn = null) : IBaseFactory {
		if (is_null($fn)) $fn = function(string $qname, string $iname) {
			return $qname === 'foo' && $iname === IInjectable::class;
		};

		$ins = $this
			->getMockBuilder(IBaseFactory::class)
			->getMock();

		$ins
			->expects($this->any())
			->method('hasInterface')
			->with($this->isType('string'), $this->isType('string'))
			->willReturnCallback($fn);

		return $ins;
	}

	private function _mockDriverAssembly(IInjector $injector, IBaseFactory $base) : IAssemblyHost {
		$ins = $this
			->getMockBuilder(IAssemblyHost::class)
			->getMock();

		$ins
			->method('getItem')
			->with($this->isType('string'))
			->willReturnCallback(function(string $key) use ($injector, $base) {
				if ($key === 'injector') return $injector;
				else if ($key === 'baseFactory') return $base;
				else $this->fail();
			});

		return $ins;
	}

	private function _mockProvider(IBaseFactory $factory = null, callable $fn = null) : AProvider {
		$injector = $this->_mockInjector();

		if (is_null($factory)) $factory = $this->_mockFactory();

		if (is_null($fn)) $fn = function(string $entity) {
			$parts = explode('?', $entity, 2);
			$config = [];

			parse_str($parts[1], $config);

			return [
				'qname' => $parts[0],
				'config' => $config
			];
		};

		$ins = $this
			->getMockBuilder(AProvider::class)
			->setConstructorArgs([ $injector, $factory ])
			->getMockForAbstractClass();

		$ins
			->expects($this->any())
			->method('_parseEntity')
			->with($this->isType('string'))
			->willReturnCallback($fn);

		return $ins;
	}

	private function _produceAccessor(array $data) {
		return new TraversableAccessor($data);
	}


	public function testDependencyConfig() {
		$injector = $this->_mockInjector();
		$factory = $this->_mockFactory();
		$assembly = $this->_mockDriverAssembly($injector, $factory);

		$this->assertEquals([[
			'type' => IInjector::TYPE_ARGUMENT,
			'data' => $injector
		], [
			'type' => IInjector::TYPE_ARGUMENT,
			'data' => $factory
		]], AProvider::getDependencyConfig($this->_produceAccessor([
			'driver' => $assembly
		])));
	}

	public function testInheritance() {
		$provider = $this->_mockProvider();

		$this->assertInstanceOf(IProvider::class, $provider);
		$this->assertInstanceOf(IInjectable::class, $provider);
		$this->assertInstanceOf(IItemAccessor::class, $provider);
	}


	public function testGetItem() {
		$provider = $this->_mockProvider();
		$ref = $provider->getItem('foo?a=b&c=d');

		$this->assertEquals([
			'qname' => 'foo',
			'config' => [
				'a' => 'b',
				'c' => 'd'
			]
		], $ref);
	}

	public function testGetItem_noName() {
		$provider = $this->_mockProvider(null, function(string $entity) {
			return [
				'config' => []
			];
		});

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('PRV malformed entity "foo?a=b&c=d"');

		$ref = $provider->getItem('foo?a=b&c=d');
	}

	public function testGetItem_badName() {
		$provider = $this->_mockProvider(null, function(string $entity) {
			return [
				'qname' => 0,
				'config' => []
			];
		});

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('PRV malformed entity "foo?a=b&c=d"');

		$ref = $provider->getItem('foo?a=b&c=d');
	}

	public function testGetItem_noConfig() {
		$provider = $this->_mockProvider(null, function(string $entity) {
			return [
				'qname' => 'foo'
			];
		});

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('PRV malformed entity "foo?a=b&c=d"');


		$ref = $provider->getItem('foo?a=b&c=d');
	}

	public function testGetItem_badConfig() {
		$provider = $this->_mockProvider(null, function(string $entitu) {
			return [
				'qname' => 'foo',
				'config' => 0
			];
		});

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('PRV malformed entity "foo?a=b&c=d"');

		$ref = $provider->getItem('foo?a=b&c=d');
	}


	public function testHasKey() {
		$provider = $this->_mockProvider();

		$this->assertTrue($provider->hasKey('foo?bar'));
		$this->assertFalse($provider->hasKey('bar?baz'));
	}

	public function testHasKey_malformed() {
		$provider = $this->_mockProvider(null, function(string $entity) {
			return [];
		});

		$this->assertFalse($provider->hasKey('foo?bar'));
	}
}
