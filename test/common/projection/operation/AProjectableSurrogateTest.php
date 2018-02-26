<?php

namespace test\common\projection\operation;

use PHPUnit\Framework\TestCase;

use eve\common\projection\IProjectable;
use eve\common\projection\operator\IProjectableSurrogate;
use eve\common\projection\operator\AProjectableSurrogate;



final class AProjectableSurrogateTest
extends TestCase
{

	public function _mockProjectable(array $projection = []) {
		$projectable = $this
			->getMockBuilder(IProjectable::class)
			->getMock();

		$projectable
			->method('getProjection')
			->willReturn($projection);

		return $projectable;
	}

	public function _mockSurrogate() {
		$operator = $this
			->getMockBuilder(AProjectableSurrogate::class)
			->getMockForAbstractClass();

		return $operator;
	}


	public function testInheritance() {
		$operator = $this->_mockSurrogate();

		$this->assertInstanceOf(IProjectableSurrogate::class, $operator);
	}

	public function testIsEqual() {
		$operator = $this->_mockSurrogate();
		$d1 = [ 'foo' => 1, 'bar' => 2 ];
		$d2 = [ 'foo' => 1, 'bar' => 2 ];
		$d3 = [ 'foo' => 1 ];
		$d4 = [ 'foo' => 3, 'bar' => 3 ];
		$d5 = [ 'bar' => 2, 'foo' => 1 ];


		$a = $this->_mockProjectable($d1);

		$this->assertTrue($operator->isEqual($a, $this->_mockProjectable($d1)));
		$this->assertTrue($operator->isEqual($a, $this->_mockProjectable($d2)));
		$this->assertFalse($operator->isEqual($a, $this->_mockProjectable($d3)));
		$this->assertFalse($operator->isEqual($a, $this->_mockProjectable($d4)));
		$this->assertFalse($operator->isEqual($a, $this->_mockProjectable($d5)));
	}
}
