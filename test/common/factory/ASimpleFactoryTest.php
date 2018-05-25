<?php

namespace test\common\factory;

use PHPUnit\Framework\TestCase;

use eve\common\IFactory;
use eve\common\factory\IBaseFactory;
use eve\common\factory\ISimpleFactory;
use eve\common\factory\ASimpleFactory;



final class ASimpleFactoryTest
extends TestCase
{

	private function _mockBaseFactory() {
		return $this
			->getMockBuilder(IBaseFactory::class)
			->getMock();
	}

	private function _mockFactory(callable $fn, IBaseFactory $base = null) {
		if (is_null($base)) $base = $this->_mockBaseFactory();

		$ins = $this
			->getMockBuilder(ASimpleFactory::class)
			->setConstructorArgs([$base])
			->setMethods([ '_produceInstance' ])
			->getMockForAbstractClass();

		$ins
			->method('_produceInstance')
			->with(
				$this->isInstanceOf(IBaseFactory::class),
				$this->isType('array')
			)
			->willReturnCallback($fn);

		return $ins;
	}


	public function testInheritance() {
		$factory = $this->_mockFactory(function() {});

		$this->assertInstanceOf(ISimpleFactory::class, $factory);
		$this->assertInstanceOf(IFactory::class, $factory);
	}


	public function testProduce() {
		$config = [ 'bar' => 2 ];
		$settings = [
			'foo' => 1,
			'bar' => 2
		];

		$base = $this->_mockBaseFactory();

		$base
			->method('callMethod')
			->with(
				$this->equalTo(\eve\common\base\ArrayOperation::class),
				$this->equalTo('merge'),
				$this->isType('array')
			)
			->willReturnCallback(function(string $qname, string $method, array $args) use ($config, $settings) {
				$this->assertEquals([], $args[0]);
				$this->assertEquals($config, $args[1]);

				return $settings;
			});

		$factory = $this->_mockFactory(function(IBaseFactory $base, array $config) use ($settings) {
			$this->assertEquals($settings, $config);

			return 'foo';
		}, $base);

		$this->assertEquals('foo', $factory->produce($config));
	}
}
