<?php

namespace test\access;

use PHPUnit\Framework\TestCase;
use eve\common\factory\ICoreFactory;
use eve\common\access\TraversableAccessor;
use eve\common\access\factory\TraversableAccessorFactory;
use eve\common\access\operator\AItemAccessorSurrogate;



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

		$this->assertInstanceOf(AItemAccessorSurrogate::class, $fab);
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

	public function testProduce_empty() {
		$fab = $this->_produceAccessorFactory();
		$ins = $fab->produce();

		$this->assertInstanceOf(TraversableAccessor::class, $ins);
	}
}
