<?php

namespace test\access;

use PHPUnit\Framework\TestCase;

use eve\access\IKeyAccessor;
use eve\access\IItemAccessor;
use eve\access\ItemAccessor;



final class ItemAccessorTest
extends TestCase
{

	private function _produceSampleData() {
		return [
			'foo' => 1,
			'bar' => 2
		];
	}

	private function _produceAccessor(array& $data = null) {
		if (is_null($data)) $data = $this->_produceSampleData();

		return new ItemAccessor($data);
	}


	public function testInheritance() {
		$ins = $this->_produceAccessor();

		$this->assertInstanceOf(IKeyAccessor::class, $ins);
		$this->assertInstanceOf(IItemAccessor::class, $ins);
	}


	public function test_useData() {
		$data = $this->_produceSampleData();
		$ins = $this->_produceAccessor($data);

		$method = new \ReflectionMethod($ins, '_useData');
		$method->setAccessible(true);

		$this->assertSame($data, $method->invoke($ins));
	}


	public function testHasKey() {
		$ins = $this->_produceAccessor();

		$this->assertTrue($ins->hasKey('foo'));
		$this->assertTrue($ins->hasKey('bar'));
		$this->assertFalse($ins->hasKey('baz'));
	}

	public function testGetItem() {
		$ins = $this->_produceAccessor();

		$this->assertEquals(1, $ins->getItem('foo'));
		$this->assertEquals(2, $ins->getItem('bar'));
	}

	public function testGetItem_noKey() {
		$ins = $this->_produceAccessor();

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage(sprintf('ACC invalid key "baz"'));

		$ins->getItem('baz');
	}


	public function testReferencing() {
		$data = $this->_produceSampleData();
		$ins = $this->_produceAccessor($data);

		$this->assertEquals(1, $ins->getItem('foo'));

		$data['foo'] = 3;

		$this->assertEquals(3, $ins->getItem('foo'));

		unset($data['foo']);

		$this->assertFalse($ins->hasKey('foo'));
		$this->assertFalse($ins->hasKey('baz'));

		$data['baz'] = 4;

		$this->assertTrue($ins->hasKey('baz'));
		$this->assertEquals(4, $ins->getItem('baz'));
	}
}
