<?php

namespace test\access;

use PHPUnit\Framework\TestCase;

use eve\common\IFactory;
use eve\common\factory\ISimpleFactory;
use eve\factory\ICoreFactory;
use eve\access\TraversableAccessorFactory;
use eve\access\TraversableAccessor;



final class TraversableAccessorFactoryTest
extends TestCase
{

	private function _mockFactory() {
		$ins = $this
			->getMockBuilder(ICoreFactory::class)
			->getMock();

		$ins
			->expects($this->any())
			->method('newInstance')
			->with($this->equalTo(TraversableAccessor::class), $this->isType('array'))
			->willReturnCallback(function(string $qname, array $args) {
				return new $qname($args[0]);
			});

		return $ins;
	}

	private function _produceAccessorFactory(ICoreFactory $factory = null) {
		if (is_null($factory)) $factory = $this->_mockFactory();

		return new TraversableAccessorFactory($factory);
	}


	public function testInheritance() {
		$fab = $this->_produceAccessorFactory();

		$this->assertInstanceOf(ISimpleFactory::class, $fab);
		$this->assertInstanceOf(IFactory::class, $fab);
	}

	public function testProduce() {
		$data = [
			'foo' => 1,
			'bar' => 2
		];
		$fab = $this->_produceAccessorFactory();
		$ins = $fab->produce($data);

		$this->assertInstanceOf(TraversableAccessor::class, $ins);
		$this->assertEquals(1, $ins->getItem('foo'));
		$this->assertEquals(2, $ins->getItem('bar'));

		$data['foo'] = 3;

		$this->assertEquals(3, $ins->getItem('foo'));
	}
}
