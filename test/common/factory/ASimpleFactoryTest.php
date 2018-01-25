<?php

namespace test\common\factory;

use PHPUnit\Framework\TestCase;

use eve\common\IFactory;
use eve\common\factory\ICoreFactory;
use eve\common\factory\ISimpleFactory;
use eve\common\factory\ASimpleFactory;



final class ASimpleFactoryTest
extends TestCase
{

	private function _mockCoreFactory() {
		return $this
			->getMockBuilder(ICoreFactory::class)
			->getMock();
	}

	private function _mockFactory(callable $fn, array $config = null, ICoreFactory $core = null) {
		if (is_null($core)) $core = $this->_mockCoreFactory();

		$methods = ['_produceInstance'];

		if (!is_null($config)) $methods[] = '_getConfigDefaults';

		$ins = $this
			->getMockBuilder(ASimpleFactory::class)
			->setConstructorArgs([$core])
			->setMethods($methods)
			->getMockForAbstractClass();

		$ins
			->expects($this->any())
			->method('_produceInstance')
			->with(
				$this->isInstanceOf(ICoreFactory::class),
				$this->isType('array')
			)
			->willReturnCallback($fn);

		if (!is_null($config)) $ins
			->expects($this->any())
			->method('_getConfigDefaults')
			->with()
			->willReturn($config);

		return $ins;
	}


	public function testInheritance() {
		$factory = $this->_mockFactory(function() {});

		$this->assertInstanceOf(ISimpleFactory::class, $factory);
		$this->assertInstanceOf(IFactory::class, $factory);
	}


	public function testProduce() {
		$a = [
			'a' => 1,
			'b' => 1,
			'c' => 1,
			'd' => [ 1 ],
			'e' => [
				'f' => 1,
				'g' => [
					'h' => 1,
					'i' => 1,
					'j' => 1,
					'k' => 1
				]
			]
		];

		$b = [
			'b' => 2,
			'c' => [ 3 ],
			'd' => 4,
			'e' => [
				'g' => [
					'i' => 5,
					'j' => [ 6 ],
					'k' => 7
				]
			]
		];

		$c = [
			'a' => 1,
			'b' => 2,
			'c' => [ 3 ],
			'd' => 4,
			'e' => [
				'f' => 1,
				'g' => [
					'h' => 1,
					'i' => 5,
					'j' => [ 6 ],
					'k' => 7
				]
			]
		];

		$factoryA = $this->_mockFactory(function(ICoreFactory $core, array $config) use ($b) {
			$this->assertEquals($b, $config);

			return 'foo';
		});

		$factoryB = $this->_mockFactory(function(ICoreFactory $core, array $config) use ($c) {
			$this->assertEquals($c, $config);

			return 'bar';
		}, $a);

		$this->assertEquals('foo', $factoryA->produce($b));
		$this->assertEquals('bar', $factoryB->produce($b));
	}
}
