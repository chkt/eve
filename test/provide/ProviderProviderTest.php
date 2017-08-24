<?php

namespace test\provide;

use PHPUnit\Framework\TestCase;

use eve\access\IItemAccessor;
use eve\access\TraversableAccessor;
use eve\entity\IEntityParser;
use eve\driver\IInjectorDriver;
use eve\inject\IInjector;
use eve\provide\IProvider;
use eve\provide\ILocator;
use eve\provide\ProviderProvider;



final class ProviderProviderTest
extends TestCase
{

	private function _mockProvider(array $items = []) : IProvider {
		$ins = $this
			->getMockBuilder(IProvider::class)
			->getMock();

		$ins
			->expects($this->any())
			->method('getItem')
			->with($this->isType('string'))
			->willReturnCallback(function(string $id) use ($items) {
				return $items[$id];
			});

		return $ins;
	}

	private function _mockInjector(array $map = []) : IInjector {
		$ins = $this
			->getMockBuilder(IInjector::class)
			->getMock();

		$ins
			->expects($this->any())
			->method('produce')
			->with($this->isType('string'))
			->willReturnCallback(function(string $qname) use ($map) {
				return $map[$qname];
			});

		return $ins;
	}

	private function _mockDriver(IInjector $injector = null, IEntityParser $parser = null) : IInjectorDriver {
		if (is_null($injector)) $injector = $this->_mockInjector();
		if (is_null($parser)) $parser = $this->_produceParser();

		$ins = $this
			->getMockBuilder(IInjectorDriver::class)
			->getMock();

		$ins
			->expects($this->once())
			->method('getInjector')
			->with()
			->willReturn($injector);

		$ins
			->expects($this->once())
			->method('getEntityParser')
			->with()
			->willReturn($parser);

		return $ins;
	}

	private function _produceParser() : IEntityParser {
		return new \eve\entity\EntityParser();
	}

	private function _produceLocator(IInjectorDriver $driver = null, array $providerNames = []) : ProviderProvider {
		if (is_null($driver)) $driver = $this->_mockDriver();

		return new ProviderProvider($driver, $providerNames);
	}

	private function _produceAccessor(array $data) {
		return new TraversableAccessor($data);
	}


	public function testDependencyConfig() {
		$driver = 'foo';
		$names = 'bar';

		$this->assertEquals([[
			'type' => IInjector::TYPE_ARGUMENT,
			'data' => $driver
		], [
			'type' => IInjector::TYPE_ARGUMENT,
			'data' => $names
		]], ProviderProvider::getDependencyConfig($this->_produceAccessor([
			'driver' => $driver,
			'providerNames' => $names
		])));
	}

	public function testInheritance() {
		$locator = $this->_produceLocator();

		$this->assertInstanceOf(ILocator::class, $locator);
		$this->assertInstanceOf(IProvider::class, $locator);
		$this->assertInstanceof(IItemAccessor::class, $locator);
	}

	public function testHasKey() {
		$injector = $this->_mockInjector([
			'fooProvider' => $this->_mockProvider()
		]);
		$driver = $this->_mockDriver($injector);

		$locator = $this->_produceLocator($driver, [
			'foo' => 'fooProvider'
		]);

		$this->assertTrue($locator->hasKey('foo'));
		$this->assertFalse($locator->hasKey('bar'));
	}

	public function testGetItem() {
		$provider = $this->_mockProvider();
		$injector = $this->_mockInjector([
			'fooProvider' => $provider
		]);
		$driver = $this->_mockDriver($injector);

		$locator = $this->_produceLocator($driver, [
			'foo' => 'fooProvider'
		]);

		$this->assertSame($provider, $locator->getItem('foo'));
	}

	public function testGetItem_invalidProvider() {
		$injector = $this->_mockInjector([
			'fooProvider' => new \stdClass()
		]);
		$driver = $this->_mockDriver($injector);

		$locator = $this->_produceLocator($driver, [
			'foo' => 'fooProvider'
		]);

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('LOC not a provider "foo"');

		$locator->getItem('foo');
	}

	public function testGetItem_noProvider() {
		$locator = $this->_produceLocator();

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('LOC unknown provider "foo"');

		$locator->getItem('foo');
	}

	public function testLocate() {
		$injector = $this->_mockInjector([
			'fooProvider' => $this->_mockProvider(['1' => 'bar'])
		]);
		$driver = $this->_mockDriver($injector);

		$locator = $this->_produceLocator($driver, [
			'foo' => 'fooProvider'
		]);

		$this->assertEquals('bar', $locator->locate('foo:1'));
	}
}
