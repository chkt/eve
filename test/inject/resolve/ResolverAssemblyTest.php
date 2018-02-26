<?php

namespace test\inject\resolve;

use PHPUnit\Framework\TestCase;

use eve\common\access\ITraversableAccessor;
use eve\common\access\AccessorException;
use eve\common\assembly\IAssemblyHost;
use eve\common\assembly\AAssemblyHost;
use eve\common\assembly\exception\InvalidKeyException;
use eve\inject\IInjector;
use eve\inject\resolve\IInjectorResolver;
use eve\inject\resolve\ResolverAssembly;



final class ResolverAssemblyTest
extends TestCase
{

	private function _mockAccessor(array $data = []) {
		$accessor = $this
			->getMockBuilder(ITraversableAccessor::class)
			->getMock();

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

	private function _mockInjector() {
		$injector = $this
			->getMockBuilder(IInjector::class)
			->getMock();

		$injector
			->method('produce')
			->with(
				$this->isType('string'),
				$this->isType('array')
			)
			->willReturnCallback(function(string $qname, array $args) {
				$resolver = $this
					->getMockBuilder(IInjectorResolver::class)
					->getMock();

				$resolver->name = $qname;
				$resolver->args = $args;

				return $resolver;
			});

		return $injector;
	}

	private function _mockDriver() {
		$injector = $this->_mockInjector();
		$driver = $this
			->getMockBuilder(IAssemblyHost::class)
			->getMock();

		$driver
			->method('getItem')
			->with($this->equalTo('injector'))
			->willReturn($injector);

		return $driver;
	}

	private function _produceAssembly(IAssemblyHost $driver = null, ITraversableAccessor $config = null) {
		if (is_null($driver)) $driver = $this->_mockDriver();
		if (is_null($config)) $config = $this->_mockAccessor();

		return new ResolverAssembly($driver, $config);
	}


	public function testInheritance() {
		$assembly = $this->_produceAssembly();

		$this->assertInstanceOf(AAssemblyHost::class, $assembly);
	}


	public function testGetItem() {
		$driver = $this->_mockDriver();
		$config = $this->_mockAccessor([ 'foo' => 'bar' ]);

		$assembly = $this->_produceAssembly($driver, $config);
		$resolver = $assembly->getItem('foo');

		$this->assertInstanceOf(IInjectorResolver::class, $resolver);
		$this->assertEquals('bar', $resolver->name);
		$this->assertArrayHasKey('driver', $resolver->args);
		$this->assertSame($driver, $resolver->args['driver']);
	}

	public function testGetItem_invalidKey() {
		$assembly = $this->_produceAssembly();

		$this->expectException(InvalidKeyException::class);
		$this->expectExceptionMessage('ASM invalid key "foo"');

		$assembly->getItem('foo');
	}
}
