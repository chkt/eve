<?php

namespace test\common\assembly;

use PHPUnit\Framework\TestCase;
use eve\common\assembly\ASingularHost;
use eve\common\assembly\exception\InvalidKeyException;



final class ASingularHostTest
extends TestCase
{

	private function _mockHost(array& $data = [], array $map = []) {
		$names = $map;

		foreach ($names as $name => & $method) $method = '_produce' . ucfirst($name);

		$host = $this
			->getMockBuilder(ASingularHost::class)
			->setConstructorArgs([ & $data ])
			->setMethods(!empty($names) ? array_values($names) : null)
			->getMock();

		foreach ($names as $name => $method) $host
			->method($method)
			->willReturn($map[$name]);

		return $host;
	}


	public function testInheritance() {
		$host = $this->_mockHost();

		$this->assertInstanceOf(\eve\common\assembly\AAssemblyHost::class, $host);
	}


	public function testHasKey() {
		$data = [ 'foo' => 1 ];
		$map = [ 'bar' => 2 ];
		$host = $this->_mockHost($data, $map);

		$this->assertTrue($host->hasKey('foo'));
		$this->assertTrue($host->hasKey('bar'));
		$this->assertArrayNotHasKey('bar', $data);
		$this->assertFalse($host->hasKey('baz'));
	}


	public function testGetItem() {
		$data = [ 'foo' => 1 ];
		$map = [ 'bar' => 2 ];
		$host = $this->_mockHost($data, $map);

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

	public function testGetItem_arguments() {
		$data = [];

		$host = $this
			->getMockBuilder(ASingularHost::class)
			->setConstructorArgs([ & $data ])
			->setMethods([ '_getFactoryArguments', '_produceFoo' ])
			->getMock();

		$host
			->expects($this->once())
			->method('_getFactoryArguments')
			->willReturn([ 'bar', 'baz' ]);

		$host
			->expects($this->once())
			->method('_produceFoo')
			->with(
				$this->equalTo('bar'),
				$this->equalTo('baz')
			)
			->willReturn('quux');

		$this->assertEquals('quux', $host->getItem('foo'));
		$this->assertArrayHasKey('foo', $data);
		$this->assertEquals('quux', $data['foo']);
		$this->assertEquals('quux', $host->getItem('foo'));
	}
}
