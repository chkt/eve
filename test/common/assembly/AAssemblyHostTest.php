<?php

namespace test\common\assembly;

use PHPUnit\Framework\TestCase;

use eve\common\IFactory;
use eve\common\IHost;
use eve\common\access\IItemAccessor;
use eve\common\access\IKeyAccessor;
use eve\common\assembly\IAssemblyHost;
use eve\common\assembly\AAssemblyHost;
use eve\common\assembly\exception\DependencyLoopException;
use eve\common\assembly\exception\InvalidKeyException;



final class AAssemblyHostTest
extends TestCase
{

	private function _mockHost(array& $data = [], callable $fn = null) {
		if (is_null($fn)) $fn = function(string $key) {
			return false;
		};

		$host = $this
			->getMockBuilder(AAssemblyHost::class)
			->setConstructorArgs([ & $data ])
			->setMethods([ '_produceItem' ])
			->getMock();

		$host
			->method('_produceItem')
			->with($this->isType('string'))
			->willReturnCallback($fn);

		return $host;
	}

	public function testInheritance() {
		$host = $this->_mockHost();

		$this->assertInstanceOf(IAssemblyHost::class, $host);
		$this->assertInstanceOf(IFactory::class, $host);
		$this->assertInstanceOf(IHost::class, $host);
		$this->assertInstanceOf(IItemAccessor::class, $host);
		$this->assertInstanceOf(IKeyAccessor::class, $host);
	}


	public function testHasKey() {
		$data = [ 'bar' => 1 ];
		$host = $this->_mockHost($data, function(string $key) {
			if ($key !== 'foo') throw new InvalidKeyException($key);

			return 0;
		});

		$this->assertTrue($host->hasKey('foo'));
		$this->assertArrayHasKey('foo', $data);
		$this->assertEquals(0, $data['foo']);
		$this->assertTrue($host->hasKey('bar'));
		$this->assertFalse($host->hasKey('baz'));
	}


	public function testGetItem() {
		$data = [];
		$counter = 0;

		$host = $this->_mockHost($data, function(string $key) use (& $counter) {
			return implode(':', [ $key, $counter++ ]);
		});

		$this->assertEquals('foo:0', $host->getItem('foo'));
		$this->assertEquals('bar:1', $host->getItem('bar'));
		$this->assertEquals('foo:0', $host->getItem('foo'));
	}

	public function testGetItem_exception() {
		$data = [];
		$host = $this->_mockHost($data, function(string $key) {
			throw new \ErrorException(sprintf('bang:%s', $key));
		});

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('bang:foo');

		$host->getItem('foo');
	}

	public function testGetItem_chain() {
		$data = [];
		$host = null;
		$host = $this->_mockHost($data, function(string $key) use (& $host) {
			$map = [
				'foo' => 'bar',
				'bar' => 'baz'
			];

			return array_key_exists($key, $map) ? $host->getItem($map[$key]) : $key;
		});

		$this->assertEquals('baz', $host->getItem('foo'));
	}

	public function testGetItem_loop() {
		$data = [];
		$host = null;
		$host = $this->_mockHost($data, function(string $key) use (& $host) {
			$map = [
				'foo' => 'bar',
				'bar' => 'foo'
			];

			return $host->getItem($map[$key]);
		});

		$this->expectException(DependencyLoopException::class);
		$this->expectExceptionMessage('ASM circular dependency "foo"');

		$host->getItem('foo');
	}
}
