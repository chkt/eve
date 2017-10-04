<?php

namespace test\access;

use PHPUnit\Framework\TestCase;

use eve\common\IFactory;
use eve\common\factory\ISimpleFactory;
use eve\common\factory\ICoreFactory;
use eve\access\TraversableMutator;
use eve\access\TraversableMutatorFactory;



final class TraversableMutatorFactoryTest
extends TestCase
{

	private function _mockFactory() {
		$ins = $this
			->getMockBuilder(ICoreFactory::class)
			->getMock();

		$ins
			->expects($this->any())
			->method('newInstance')
			->with($this->equalTo(TraversableMutator::class), $this->isType('array'))
			->willReturnCallback(function(string $qname, array $args) {
				return new $qname($args[0]);
			});

		return $ins;
	}

	private function _produceMutatorFactory(ICoreFactory $factory = null) {
		if (is_null($factory)) $factory = $this->_mockFactory();

		return new TraversableMutatorFactory($factory);
	}


	public function testInheritance() {
		$fab = $this->_produceMutatorFactory();

		$this->assertInstanceOf(ISimpleFactory::class, $fab);
		$this->assertInstanceOf(IFactory::class, $fab);
	}

	public function testProduce() {
		$data = [
			'foo' => 1,
			'bar' => 2
		];
		$fab = $this->_produceMutatorFactory();
		$ins = $fab->produce($data);

		$this->assertInstanceOf(TraversableMutator::class, $ins);
		$this->assertEquals(1, $ins->getItem('foo'));
		$this->assertEquals(2, $ins->getItem('bar'));

		$data['foo'] = 3;

		$this->assertEquals(3, $ins->getItem('foo'));
	}
}
