<?php

namespace test\access;

use PHPUnit\Framework\TestCase;

use eve\common\IGenerateable;
use eve\common\projection\IProjectable;
use eve\common\projection\ICompactProjectable;
use eve\common\access\ItemAccessor;
use eve\common\access\TraversableAccessor;



final class TraversableAccessorTest
extends TestCase
{

	private function _mockProjectable(array& $data = null) {
		if (is_null($data)) $data = $this->_produceSampleData();

		$proj = $this
			->getMockBuilder(IProjectable::class)
			->getMock();

		$proj
			->method('getProjection')
			->willReturnReference($data);

		return $proj;
	}


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


	public function testInheritance() {
		$ins = $this->_produceInstance();

		$this->assertInstanceOf(ItemAccessor::class, $ins);
		$this->assertInstanceOf(IGenerateable::class, $ins);
		$this->assertInstanceOf(ICompactProjectable::class, $ins);
		$this->assertInstanceOf(IProjectable::class, $ins);
	}


	public function testIsEqual() {
		$ad = [ 'foo' => 1 ];
		$a = $this->_mockProjectable($ad);
		$b = $this->_produceInstance();

		$this->assertFalse($b->isEqual($a));

		$ad['bar'] = 2;

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
