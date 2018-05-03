<?php

namespace test\common\access\operator;

use PHPUnit\Framework\TestCase;

use eve\common\access\ITraversableAccessor;
use eve\common\access\exception\IAccessorException;
use eve\common\access\operator\AItemAccessorComposition;



final class AItemAccessorCompositionTest
extends TestCase
{

	public function _mockAccessor(array $data = []) {
		$access = $this
			->getMockBuilder(ITraversableAccessor::class)
			->getMock();

		$access
			->method('getItem')
			->with($this->isType('string'))
			->willReturnCallback(function(string $key) use ($data) {
				return $data[$key];
			});

		$access
			->method('getProjection')
			->willReturn($data);

		return $access;
	}


	public function _mockComposition() {
		$operator = $this
			->getMockBuilder(AItemAccessorComposition::class)
			->getMockForAbstractClass();

		$operator
			->method('produce')
			->with($this->isType('array'))
			->willReturnCallback(function(array $data) {
				return $this->_mockAccessor($data);
			});

		return $operator;
	}


	public function testInheritance() {
		$operator = $this->_mockComposition();

		$this->assertInstanceOf(\eve\common\factory\ISimpleFactory::class, $operator);
		$this->assertInstanceOf(\eve\common\access\operator\IItemAccessorSurrogate::class, $operator);
		$this->assertInstanceOf(\eve\common\access\operator\IItemAccessorComposition::class, $operator);
		$this->assertInstanceOf(\eve\common\projection\operator\IProjectableSurrogate::class, $operator);
		$this->assertInstanceOf(\eve\common\projection\operator\AProjectableSurrogate::class, $operator);
	}


	public function testSelect() {
		$operator = $this->_mockComposition();
		$a = $this->_mockAccessor([ 'foo' => [ 'bar' => 1 ]]);
		$b = $operator->select($a, 'foo');

		$this->assertInstanceOf(ITraversableAccessor::class, $b);
		$this->assertEquals(['bar' => 1], $b->getProjection());
	}

	public function testSelect_notNested() {
		$operator = $this->_mockComposition();
		$a = $this->_mockAccessor([ 'foo' => 1 ]);

		$this->expectException(IAccessorException::class);
		$this->expectExceptionMessage('ACC invalid key "foo"');

		$operator->select($a, 'foo');
	}
}
