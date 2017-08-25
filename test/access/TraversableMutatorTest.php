<?php

namespace test\access;

use PHPUnit\Framework\TestCase;

use eve\access\IKeyMutator;
use eve\access\IItemMutator;
use eve\access\TraversableAccessor;
use eve\access\TraversableMutator;



final class TraversableMutatorTest
extends TestCase
{

	private function _produceSampleData() : array {
		return [
			'foo' => 1,
			'bar' => 2
		];
	}

	private function _produceInstance(array& $data = null) : TraversableMutator {
		if (is_null($data)) $data = $this->_produceSampleData();

		return new TraversableMutator($data);
	}


	public function testInheritance() {
		$ins = $this->_produceInstance();

		$this->assertInstanceOf(IKeyMutator::class, $ins);
		$this->assertInstanceOf(IItemMutator::class, $ins);
		$this->assertInstanceOf(TraversableAccessor::class, $ins);
	}


	public function testRemoveKey() {
		$ins = $this->_produceInstance();

		$this->assertTrue($ins->hasKey('foo'));
		$this->assertSame($ins, $ins->removeKey('foo'));
		$this->assertFalse($ins->hasKey('foo'));
	}

	public function testRemoveKey_noKey() {
		$ins = $this->_produceInstance();

		$this->assertSame($ins, $ins->removeKey('baz'));
	}

	public function testSetItem() {
		$ins = $this->_produceInstance();

		$this->assertSame($ins, $ins->setItem('foo', 3));
		$this->assertEquals(3, $ins->getItem('foo'));
		$this->assertSame($ins, $ins->setItem('baz', 4));
		$this->assertEquals(4, $ins->getItem('baz'));
	}

	public function testSetItem_referencing() {
		$data = $this->_produceSampleData();
		$ins = $this->_produceInstance($data);

		$this->assertArrayNotHasKey('baz', $data);
		$ins->setItem('baz', 3);
		$this->assertArrayHasKey('baz', $data);
		$this->assertEquals(3, $data['baz']);
	}
}
