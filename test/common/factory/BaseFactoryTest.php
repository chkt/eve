<?php

namespace test\common\factory;

use PHPUnit\Framework\TestCase;

use eve\common\IFactory;
use eve\common\base\IMethodProxy;
use eve\common\factory\IBaseFactory;
use eve\common\factory\BaseFactory;



final class BaseFactoryTest
extends TestCase
{

	private function _produceFactory() {
		return new BaseFactory();
	}


	public function testInheritance() {
		$fab = $this->_produceFactory();

		$this->assertInstanceOf(IBaseFactory::class, $fab);
		$this->assertInstanceOf(\eve\common\factory\IInstancingFactory::class, $fab);
		$this->assertInstanceOf(IMethodProxy::class, $fab);
		$this->assertInstanceOf(IFactory::class, $fab);
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


	public function testProduce() {
		$fab = $this->_produceFactory();
		$ins = $fab->produce(TestClass::class, ['1', '2', '3']);

		$this->assertInstanceOf(TestClass::class, $ins);
		$this->assertEquals('1', $ins->foo);
		$this->assertEquals('2', $ins->bar);
		$this->assertEquals('3', $ins->baz);
	}
}
