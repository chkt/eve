<?php

namespace test\access;

use PHPUnit\Framework\TestCase;

use eve\common\ISimpleFactory;
use eve\factory\IFactory;
use eve\access\TraversableAccessorFactory;
use eve\access\TraversableAccessor;



final class TraversableAccessorFactoryTest
extends TestCase
{

	private function _mockFactory() {
		$ins = $this
			->getMockBuilder(IFactory::class)
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

	private function _produceAccessorFactory(IFactory $factory = null) {
		if (is_null($factory)) $factory = $this->_mockFactory();

		return new TraversableAccessorFactory($factory);
	}


	public function testInheritance() {
		$fab = $this->_produceAccessorFactory();

		$this->assertInstanceOf(ISimpleFactory::class, $fab);
	}

	public function testInstance() {
		$data = [
			'foo' => 1,
			'bar' => 2
		];
		$fab = $this->_produceAccessorFactory();
		$ins = $fab->instance($data);

		$this->assertInstanceOf(TraversableAccessor::class, $ins);
		$this->assertEquals(1, $ins->getItem('foo'));
		$this->assertEquals(2, $ins->getItem('bar'));

		$data['foo'] = 3;

		$this->assertEquals(3, $ins->getItem('foo'));
	}
}
