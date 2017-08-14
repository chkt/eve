<?php

namespace test\factory;

use PHPUnit\Framework\TestCase;

use eve\factory\Factory;



final class FactoryTest
extends TestCase
{

	private function _produceFactory() {
		return new Factory();
	}


	public function testHasInterface() {
		$fab = $this->_produceFactory();

		$this->assertTrue($fab->hasInterface(TestClass::class, TestInterface::class));
		$this->assertFalse($fab->hasInterface(TestClass::class, OtherInterface::class));
		$this->assertFalse($fab->hasInterface(TestClass::class, TestParentClass::class));
		$this->assertFalse($fab->hasInterface(TestClass::class, '\test\factory\NotAnInterface'));
	}


	public function testCallMethod() {
		$fab = $this->_produceFactory();

		$this->assertEquals('1/2/3', $fab->callMethod(TestClass::class, 'staticMethod', ['1', '2', '3']));
		$this->assertEquals('1/2/4', $fab->callMethod(TestClass::class, 'memberMethod', ['1', '2']));
	}

	public function testCallMethod_notStatic() {
		$fab = $this->_produceFactory();

		$this->expectException(\Error::class);

		$fab->callMethod(TestClass::class, 'thisMethod', ['1', '2']);
	}


	public function testNewInstance() {
		$fab = $this->_produceFactory();
		$ins = $fab->newInstance(TestClass::class, ['1', '2', '3']);

		$this->assertInstanceOf(TestClass::class, $ins);
		$this->assertEquals('1', $ins->foo);
		$this->assertEquals('2', $ins->bar);
		$this->assertEquals('3', $ins->baz);
	}
}
