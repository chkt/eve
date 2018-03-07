<?php

namespace test\common\access\operator;

use PHPUnit\Framework\TestCase;

use eve\common\projection\IProjectable;
use eve\common\access\exception\IAccessorException;
use eve\common\access\ITraversableAccessor;
use eve\common\access\operator\AItemAccessorComposition;



final class AItemAccessorCompositionTest
extends TestCase
{

	public function _mockProjectable(string $qname = IProjectable::class, array $data = []) {
		$projectable = $this
			->getMockBuilder($qname)
			->getMock();

		$projectable
			->method('getProjection')
			->willReturn($data);

		return $projectable;
	}


	public function _mockComposition() {
		$operator = $this
			->getMockBuilder(AItemAccessorComposition::class)
			->getMockForAbstractClass();

		$operator
			->method('produce')
			->with($this->isType('array'))
			->willReturnCallback(function(array $data) {
				return $this->_mockProjectable(ITraversableAccessor::class, $data);
			});

		return $operator;
	}


	public function testInheritance() {
		$operator = $this->_mockComposition();

		$this->assertInstanceOf(\eve\common\projection\operator\IProjectableSurrogate::class, $operator);
		$this->assertInstanceOf(\eve\common\projection\operator\AProjectableSurrogate::class, $operator);
	}


	public function testSelect() {
		$operator = $this->_mockComposition();
		$a = $this->_mockProjectable(IProjectable::class, [ 'foo' => [ 'bar' => 1 ]]);
		$b = $operator->select($a, 'foo');

		$this->assertInstanceOf(IProjectable::class, $b);
		$this->assertEquals(['bar' => 1], $b->getProjection());
	}

	public function testSelect_noKey() {
		$operator = $this->_mockComposition();
		$a = $this->_mockProjectable();

		$this->expectException(IAccessorException::class);
		$this->expectExceptionMessage('ACC invalid key "foo"');

		$operator->select($a, 'foo');
	}

	public function testSelect_notNested() {
		$operator = $this->_mockComposition();
		$a = $this->_mockProjectable(IProjectable::class, [ 'foo' => 1 ]);

		$this->expectException(IAccessorException::class);
		$this->expectExceptionMessage('ACC invalid key "foo"');

		$operator->select($a, 'foo');
	}
}
