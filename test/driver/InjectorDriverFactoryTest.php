<?php

namespace test\driver;

use PHPUnit\Framework\TestCase;

use eve\common\IFactory;
use eve\common\ISimpleFactory;
use eve\factory\ICoreFactory;
use eve\access\ITraversableAccessor;
use eve\access\IItemMutator;
use eve\access\TraversableAccessor;
use eve\entity\IEntityParser;
use eve\inject\IInjector;
use eve\provide\ILocator;
use eve\driver\IInjectorHost;
use eve\driver\IInjectorDriver;
use eve\driver\InjectorDriverFactory;



final class InjectorDriverFactoryTest
extends TestCase
{

	private function _mockInterface(string $qname) {
		return $this
			->getMockBuilder($qname)
			->getMock();
	}

	private function _mockFactory() : ICoreFactory {
		$ins = $this
			->getMockBuilder(ICoreFactory::class)
			->getMock();

		$ins
			->expects($this->exactly(2))
			->method('newInstance')
			->with($this->logicalOr(
				$this->equalTo(\eve\driver\InjectorDriver::class),
				$this->equalTo(\eve\driver\InjectorHost::class)
			))
			->willReturnCallback(function(string $qname, array $args) {
				return new $qname(...$args);
			});

		return $ins;
	}

	private function _mockAccessorFactory() : ISimpleFactory {
		$ins = $this
			->getMockBuilder(ISimpleFactory::class)
			->getMock();


		$ins
			->expects($this->any())
			->method('produce')
			->with($this->isType('array'))
			->willReturnCallback(function(array& $data) {
				return new TraversableAccessor($data);
			});

		return $ins;
	}


	private function _produceFactory() {
		return new InjectorDriverFactory();
	}


	public function test_array_merge_deep() {
		$this->assertEquals([
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
		], \eve\driver\array_merge_deep([
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
		], [
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
		]));
	}

	public function testInheritance() {
		$fab = $this->_produceFactory();

		$this->assertInstanceOf(ISimpleFactory::class, $fab);
		$this->assertInstanceOf(IFactory::class, $fab);
	}

	public function testProduce() {
		$driver = $this
			->_produceFactory()
			->produce();

		$this->assertInstanceOf(IInjectorDriver::class, $driver);
		$this->assertInstanceOf(ICoreFactory::class, $driver->getFactory());
		$this->assertInstanceOf(ISimpleFactory::class, $driver->getAccessorFactory());
		$this->assertInstanceOf(IEntityParser::class, $driver->getEntityParser());
		$this->assertInstanceOf(ITraversableAccessor::class, $driver->getReferences());
		$this->assertInstanceOf(IInjector::class, $driver->getInjector());
		$this->assertInstanceOf(ILocator::class, $driver->getLocator());
		$this->assertInstanceOf(IInjectorHost::class, $driver->getHost());
	}

	public function testProduce_instances() {
		$factory = $this->_mockFactory();
		$accessor = $this->_mockAccessorFactory();
		$parser = $this->_mockInterface(IEntityParser::class);
		$cache = $this->_mockInterface(IItemMutator::class);
		$injector = $this->_mockInterface(IInjector::class);
		$locator = $this->_mockInterface(ILocator::class);

		$config = [
			'factory' => $factory,
			'accessorFactory' => $accessor,
			'entityParser' => $parser,
			'instanceCache' => $cache,
			'injector' => $injector,
			'locator' => $locator
		];

		$driver = $this
			->_produceFactory()
			->produce($config);

		$this->assertSame($factory, $driver->getFactory());
		$this->assertSame($accessor, $driver->getAccessorFactory());
		$this->assertSame($parser, $driver->getEntityParser());
		$this->assertSame($injector, $driver->getInjector());
		$this->assertSame($locator, $driver->getLocator());
	}
}
