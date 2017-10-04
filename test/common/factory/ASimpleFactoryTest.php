<?php

namespace test\common\factory;

use PHPUnit\Framework\TestCase;

use eve\common\IFactory;
use eve\common\factory\ISimpleFactory;
use eve\common\factory\ASimpleFactory;



final class ASimpleFactoryTest
extends TestCase
{

	private function _mockFactory(array $defaults = [], callable $fn) {
		$ins = $this
			->getMockBuilder(ASimpleFactory::class)
			->setMethods(['_getConfigDefaults', '_produceInstance'])
			->getMockForAbstractClass();

		$ins
			->expects($this->any())
			->method('_getConfigDefaults')
			->with()
			->willReturn($defaults);

		$ins
			->expects($this->any())
			->method('_produceInstance')
			->with($this->isType('array'))
			->willReturnCallback($fn);

		return $ins;
	}


	public function testInheritance() {
		$factory = $this->_mockFactory([], function() {});

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

		$factory = $this->_mockFactory($a, function(array $config) use ($c) {
			$this->assertEquals($c, $config);

			return 'foo';
		});

		$this->assertEquals('foo', $factory->produce($b));
	}
}
