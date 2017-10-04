<?php

namespace test\factory;

use eve\factory\ASimpleFactory;
use PHPUnit\Framework\TestCase;



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
