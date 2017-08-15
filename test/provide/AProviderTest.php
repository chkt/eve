<?php

namespace test\provide;

use PHPUnit\Framework\TestCase;

use eve\access\IItemAccessor;
use eve\access\TraversableAccessor;
use eve\driver\IInjectorDriver;
use eve\inject\IInjectable;
use eve\inject\IInjector;
use eve\provide\IProvider;
use eve\provide\AProvider;



class AProviderTest
extends TestCase
{

	private function _mockInjector(callable $fn = null) : IInjector {
		if (is_null($fn)) $fn = function(string $qname, array $config) {
			return [
				'qname' => $qname,
				'config' => $config
			];
		};

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
			->willReturnCallback($fn);

		return $ins;
	}

	private function _mockDriver(IInjector $injector = null) : IInjectorDriver {
		if (is_null($injector)) $injector = $this->_mockInjector();

		$ins = $this
			->getMockBuilder(IInjectorDriver::class)
			->getMock();

		$ins
			->expects($this->once())
			->method('getInjector')
			->with()
			->willReturn($injector);

		return $ins;
	}

	private function _mockProvider(IInjector $injector = null, callable $fn = null) : AProvider {
		if (is_null($injector)) $injector = $this->_mockInjector();

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
			->setConstructorArgs([ $injector ])
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
		$driver = $this->_mockDriver($injector);

		$this->assertEquals([[
			'type' => IInjector::TYPE_ARGUMENT,
			'data' => $injector
		]], AProvider::getDependencyConfig($this->_produceAccessor([
			'driver' => $driver
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

	public function testGetItem_malformed() {
		$provider = $this->_mockProvider(null, function(string $entity) {
			return [];
		});

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('PRV malformed entity "foo?a=b&c=d"');

		$ref = $provider->getItem('foo?a=b&c=d');
	}

	public function testHasKey() {
		$injector = $this->_mockInjector(function(string $qname, array $config = []) {
			if ($qname === 'foo') return 'foo';
			else throw new \ReflectionException();
		});
		$provider = $this->_mockProvider($injector);

		$this->assertTrue($provider->hasKey('foo?bar'));
		$this->assertFalse($provider->hasKey('bar?baz'));
	}
}
