<?php

namespace test\access;

use PHPUnit\Framework\TestCase;

use eve\common\IGenerateable;
use eve\common\IProjectable;
use eve\access\IKeyAccessor;
use eve\access\IItemAccessor;
use eve\access\TraversableAccessor;



final class TraversableAccessorTest
extends TestCase
{

	private function _produceSampleData() : array {
		return [
			'foo' => 1,
			'bar' => 2
		];
	}

	private function _produceInstance(array& $data = null) : TraversableAccessor {
		if (is_null($data)) $data = $this->_produceSampleData();

		return new TraversableAccessor($data);
	}


	public function testInhertance() {
		$ins = $this->_produceInstance();

		$this->assertInstanceOf(IKeyAccessor::class, $ins);
		$this->assertInstanceOf(IItemAccessor::class, $ins);
		$this->assertInstanceOf(IGenerateable::class, $ins);
		$this->assertInstanceOf(IProjectable::class, $ins);
	}


	public function testHasKey() {
		$ins = $this->_produceInstance();

		$this->assertTrue($ins->hasKey('foo'));
		$this->assertTrue($ins->hasKey('bar'));
		$this->assertFalse($ins->hasKey('baz'));
	}

	public function testGetItem() {
		$ins = $this->_produceInstance();

		$this->assertEquals(1, $ins->getItem('foo'));
		$this->assertEquals(2, $ins->getItem('bar'));
	}

	public function testGetItem_referencing() {
		$data = $this->_produceSampleData();
		$ins = $this->_produceInstance($data);

		$this->assertFalse($ins->hasKey('baz'));

		$data['baz'] = 3;

		$this->assertTrue($ins->hasKey('baz'));
		$this->assertEquals(3, $ins->getItem('baz'));
	}

	public function testGetItem_noKey() {
		$ins = $this->_produceInstance();

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('ACC invalid key "baz"');
		$this->expectExceptionCode(0);

		$ins->getItem('baz');
	}


	public function testIsEqual() {
		$ad = [ 'foo' => 1 ];
		$a = $this->_produceInstance($ad);
		$b = $this->_produceInstance();

		$this->assertFalse($a->isEqual($b));
		$this->assertFalse($b->isEqual($a));

		$ad['bar'] = 2;

		$this->assertTrue($a->isEqual($b));
		$this->assertTrue($b->isEqual($a));
	}


	public function testIterate() {
		$data = $this->_produceSampleData();
		$ins = $this->_produceInstance($data);
		$gen = $ins->iterate();

		$this->assertInstanceOf(\Generator::class, $gen);

		$items = [];

		foreach ($gen as $key => $value) $items[$key] = $value;

		$this->assertEquals($data, $items);
	}

	public function testGetProjection() {
		$data = $this->_produceSampleData();
		$ins = $this->_produceInstance($data);

		$this->assertEquals($data, $ins->getProjection());
	}
}
