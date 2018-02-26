<?php

namespace test\provide;

use PHPUnit\Framework\TestCase;

use eve\common\access\IItemAccessor;
use eve\common\access\TraversableAccessor;
use eve\common\access\AccessorException;
use eve\common\assembly\IAssemblyHost;
use eve\entity\IEntityParser;
use eve\inject\IInjector;
use eve\provide\IProvider;
use eve\provide\ILocator;
use eve\provide\ProviderProvider;



final class ProviderProviderTest
extends TestCase
{

	private function _mockInterface(string $qname, array $args = []) {
		$ins = $this
			->getMockBuilder($qname)
			->getMock();

		foreach ($args as $key => & $arg) {
			$prop = (is_numeric($key) ? 'p' : '') . $key;

			$ins->$prop =& $arg;
		}

		return $ins;
	}

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

	private function _mockProviderAssembly(array $map = []) {
		$ins = $this->_mockInterface(IAssemblyHost::class);

		$ins
			->method('hasKey')
			->with($this->isType('string'))
			->willReturnCallback(function(string $key) use ($map) {
				return array_key_exists($key, $map);
			});

		$ins
			->method('getItem')
			->with($this->isType('string'))
			->willReturnCallback(function(string $key) use ($map) {
				if (!array_key_exists($key, $map)) $this->fail($key);

				return $map[$key];
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

	private function _mockDriverAssembly(
		IInjector $injector = null,
		IEntityParser $parser = null,
		IAssemblyHost $providers = null
	) : IAssemblyHost {
		if (is_null($injector)) $injector = $this->_mockInjector();
		if (is_null($parser)) $parser = $this->_produceParser();
		if (is_null($providers)) $providers = $this->_mockInterface(IAssemblyHost::class);

		$ins = $this
			->getMockBuilder(IAssemblyHost::class)
			->getMock();

		$ins
			->method('getItem')
			->with($this->isType('string'))
			->willReturnCallback(function(string $key) use ($injector, $parser, $providers) {
				if ($key === 'injector') return $injector;
				else if ($key === 'entityParser') return $parser;
				else if ($key === 'providerAssembly') return $providers;
				else $this->fail($key);
			});

		return $ins;
	}

	private function _produceParser() : IEntityParser {
		return new \eve\entity\EntityParser();
	}

	private function _produceLocator(IAssemblyHost $driver = null) : ProviderProvider {
		if (is_null($driver)) $driver = $this->_mockDriverAssembly();

		return new ProviderProvider($driver);
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
		$providers = $this->_mockProviderAssembly([
			'foo' => $this->_mockProvider()
		]);

		$driver = $this->_mockDriverAssembly(null, null, $providers);
		$locator = $this->_produceLocator($driver);

		$this->assertTrue($locator->hasKey('foo'));
		$this->assertFalse($locator->hasKey('bar'));
	}

	public function testGetItem() {
		$provider = $this->_mockProvider();
		$providers = $this->_mockProviderAssembly([
			'foo' => $provider
		]);

		$driver = $this->_mockDriverAssembly(null, null, $providers);
		$locator = $this->_produceLocator($driver);

		$this->assertSame($provider, $locator->getItem('foo'));
	}

	public function testLocate() {
		$providers = $this->_mockProviderAssembly([
			'foo' => $this->_mockProvider(['1' => 'bar'])
		]);

		$driver = $this->_mockDriverAssembly(null, null, $providers);
		$locator = $this->_produceLocator($driver);

		$this->assertEquals('bar', $locator->locate('foo:1'));
	}
}
