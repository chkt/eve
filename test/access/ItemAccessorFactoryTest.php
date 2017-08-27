<?php

namespace test\access;

use PHPUnit\Framework\TestCase;

use eve\common\IFactory;
use eve\common\ISimpleFactory;
use eve\factory\ICoreFactory;
use eve\access\ItemAccessor;
use eve\access\ItemAccessorFactory;



final class ItemAccessorFactoryTest
extends TestCase
{

	private function _mockCoreFactory() : ICoreFactory {
		$ins = $this
			->getMockBuilder(ICoreFactory::class)
			->getMock();

		$ins
			->expects($this->any())
			->method('newInstance')
			->with($this->equalTo(ItemAccessor::class), $this->isType('array'))
			->willReturnCallback(function(string $qname, array $args) {
				return new $qname($args[0]);
			});

		return $ins;
	}


	private function _produceAccessorFactory(ICoreFactory $fab = null) : ItemAccessorFactory {
		if (is_null($fab)) $fab = $this->_mockCoreFactory();

		return new ItemAccessorFactory($fab);
	}


	public function testInheritance() {
		$ins = $this->_produceAccessorFactory();

		$this->assertInstanceOf(ISimpleFactory::class, $ins);
		$this->assertInstanceOf(IFactory::class, $ins);
	}


	public function testProduce() {
		$data = [
			'foo' => 1,
			'bar' => 2
		];
		$fab = $this->_produceAccessorFactory();
		$ins = $fab->produce($data);

		$this->assertInstanceOf(ItemAccessor::class, $ins);
		$this->assertEquals(1, $ins->getItem('foo'));
		$this->assertEquals(2, $ins->getItem('bar'));

		$data['foo'] = 3;

		$this->assertEquals(3, $ins->getItem('foo'));
	}
}
