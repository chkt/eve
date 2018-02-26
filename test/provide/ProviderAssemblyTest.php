<?php

namespace test\provide;

use PHPUnit\Framework\TestCase;

use eve\common\access\ITraversableAccessor;
use eve\common\access\AccessorException;
use eve\common\factory\ISimpleFactory;
use eve\common\assembly\IAssemblyHost;
use eve\common\assembly\AAssemblyHost;
use eve\inject\IInjector;
use eve\provide\IProvider;
use eve\provide\ProviderAssembly;



final class ProviderAssemblyTest
extends TestCase
{

	private function _mockInterface(string $qname, array $args = []) {
		$ins = $this
			->getMockBuilder($qname)
			->getMock();

		foreach($args as $key => & $arg) {
			$prop = (is_numeric($key) ? 'p' : '') . $key;

			$ins->$prop =& $arg;
		}

		return $ins;
	}

	private function _mockAccessor(array $data = []) {
		$accessor = $this->_mockInterface(ITraversableAccessor::class);

		$accessor
			->method('hasKey')
			->with($this->isType('string'))
			->willReturnCallback(function(string $key) use ($data) {
				return array_key_exists($key, $data);
			});

		$accessor
			->method('getItem')
			->with($this->isType('string'))
			->willReturnCallback(function(string $key) use ($data) {
				if (!array_key_exists($key, $data)) throw new AccessorException($key);

				return $data[$key];
			});

		return $accessor;
	}

	private function _mockAccessorFactory() {
		$accessor = $this->_mockInterface(ISimpleFactory::class);

		$accessor
			->method('produce')
			->with($this->isType('array'))
			->willReturnCallback(function(array& $data) {
				return $this->_mockInterface(ITraversableAccessor::class, [ & $data ]);
			});

		return $accessor;
	}

	private function _mockInjector() {
		$injector = $this->_mockInterface(IInjector::class);

		$injector
			->method('produce')
			->with(
				$this->isType('string'),
				$this->logicalOr(
					$this->isType('array'),
					$this->isNull()
				)
			)
			->willReturnCallback(function(string $qname, array $args = []) {
				$provider = $this
					->getMockBuilder(IProvider::class)
					->getMock();

				$provider->name = $qname;
				$provider->args = $args;

				return $provider;
			});

		return $injector;
	}

	private function _mockDriverAssembly() : IAssemblyHost {
		$injector = $this->_mockInjector();
		$accessor = $this->_mockAccessorFactory();

		$driver = $this->_mockInterface(IAssemblyHost::class);

		$driver
			->method('getItem')
			->with($this->isType('string'))
			->willReturnCallback(function(string $key) use ($injector, $accessor) {
				if ($key === 'injector') return $injector;
				else if ($key === 'accessorFactory') return $accessor;
				else $this->fail($key);
			});

		return $driver;
	}

	private function _produceAssembly(IAssemblyHost $driverAssembly = null, ITraversableAccessor $config = null) {
		if (is_null($driverAssembly)) $driverAssembly = $this->_mockDriverAssembly();
		if (is_null($config)) $config = $this->_mockAccessor();

		return new ProviderAssembly($driverAssembly, $config);
	}


	public function testInheritance() {
		$assembly = $this->_produceAssembly();

		$this->assertInstanceOf(AAssemblyHost::class, $assembly);
	}

	public function testGetItem_string() {
		$config = $this->_mockAccessor([ 'foo' => 'bar' ]);
		$driver = $this->_mockDriverAssembly();

		$assembly = $this->_produceAssembly($driver, $config);
		$provider = $assembly->getItem('foo');

		$this->assertInstanceOf(IProvider::class, $provider);
		$this->assertEquals('bar', $provider->name);
		$this->assertArrayHasKey('driver', $provider->args);
		$this->assertSame($driver, $provider->args['driver']);
		$this->assertArrayHasKey('config', $provider->args);
		$this->assertInstanceOf(ITraversableAccessor::class, $provider->args['config']);
		$this->assertObjectHasAttribute('p0', $provider->args['config']);
		$this->assertInternalType('array', $provider->args['config']->p0);
		$this->assertEmpty($provider->args['config']->p0);
	}

	public function testGetItem_array() {
		$opts = [ 'baz', 'quux' ];
		$config = $this->_mockAccessor([ 'foo' => [
			'qname' => 'bar',
			'config' => $opts
		]]);

		$assembly = $this->_produceAssembly(null, $config);
		$provider = $assembly->getItem('foo');

		$this->assertEquals('bar', $provider->name);
		$this->assertSame($opts, $provider->args['config']->p0);
	}
}
