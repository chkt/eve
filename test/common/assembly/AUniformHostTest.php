<?php

namespace test\common\assembly;

use PHPUnit\Framework\TestCase;
use eve\common\access\IItemAccessor;
use eve\common\assembly\AUniformHost;
use eve\common\assembly\exception\InvalidKeyException;



final class AUniformHostTest
extends TestCase
{

	private function _mockItemAccessor(array $data = []) {
		$access = $this
			->getMockBuilder(IItemAccessor::class)
			->getMock();

		$access
			->method('hasKey')
			->with($this->isType('string'))
			->willReturnCallback(function (string $key) use ($data) {
				return array_key_exists($key, $data);
			});

		$access
			->method('getItem')
			->with($this->isType('string'))
			->willReturnCallback(function(string $key) use ($data) {
				return $data[$key];
			});

		return $access;
	}

	private function _mockHost(array& $data = [], IItemAccessor $map = null, callable $fn = null) {
		if (is_null($map)) $map = $this->_mockItemAccessor();
		if (is_null($fn)) $fn = function(string $key) {
			return null;
		};

		$host = $this
			->getMockBuilder(AUniformHost::class)
			->setConstructorArgs([ $map, & $data ])
			->setMethods([ '_produceFromMap' ])
			->getMock();

		$host
			->method('_produceFromMap')
			->with(
				$this->isInstanceOf(IItemAccessor::class),
				$this->isType('string')
			)
			->willReturnCallback($fn);

		return $host;
	}


	public function testInheritance() {
		$host = $this->_mockHost();

		$this->assertInstanceOf(\eve\common\assembly\AAssemblyHost::class, $host);
	}


	public function testHasKey() {
		$map = $this->_mockItemAccessor([ 'bar' => 2 ]);
		$data = [ 'foo' => 1 ];
		$host = $this->_mockHost($data, $map, function(IItemAccessor $map, string $key) {
			$this->fail($key);
		});

		$this->assertTrue($host->hasKey('foo'));
		$this->assertTrue($host->hasKey('bar'));
		$this->assertArrayNotHasKey('bar', $data);
		$this->assertFalse($host->hasKey('baz'));
		$this->assertArrayNotHasKey('baz', $data);
	}


	public function testGetItem() {
		$map = $this->_mockItemAccessor([ 'bar' =>  2 ]);
		$data = [ 'foo' => 1 ];
		$host = $this->_mockHost($data, $map, function(IItemAccessor $map, string $key) {
			return $map->getItem($key);
		});

		$this->assertEquals(1, $host->getItem('foo'));
		$this->assertEquals(2, $host->getItem('bar'));
		$this->assertArrayHasKey('bar', $data);
		$this->assertEquals(2, $data['bar']);
	}

	public function testGetItem_invalid() {
		$host = $this->_mockHost();

		$this->expectException(InvalidKeyException::class);

		$host->getItem('foo');
	}
}
