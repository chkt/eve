<?php

namespace test\driver;

use PHPUnit\Framework\TestCase;

use eve\common\factory\ISimpleFactory;
use eve\common\factory\ASimpleFactory;
use eve\factory\ICoreFactory;
use eve\access\ITraversableAccessor;
use eve\access\IItemMutator;
use eve\access\TraversableAccessor;
use eve\entity\IEntityParser;
use eve\inject\IInjector;
use eve\provide\ILocator;
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
			->expects($this->once())
			->method('newInstance')
			->with($this->equalTo(\eve\driver\InjectorDriver::class))
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


	public function testInheritance() {
		$fab = $this->_produceFactory();

		$this->assertInstanceOf(ASimpleFactory::class, $fab);
	}

	public function testProduce() {
		$driver = $this
			->_produceFactory()
			->produce();

		$this->assertInstanceOf(IInjectorDriver::class, $driver);
		$this->assertInstanceOf(ICoreFactory::class, $driver->getCoreFactory());
		$this->assertInstanceOf(ISimpleFactory::class, $driver->getAccessorFactory());
		$this->assertInstanceOf(IEntityParser::class, $driver->getEntityParser());
		$this->assertInstanceOf(ITraversableAccessor::class, $driver->getReferences());
		$this->assertInstanceOf(IInjector::class, $driver->getInjector());
		$this->assertInstanceOf(ILocator::class, $driver->getLocator());
	}

	public function testProduce_instances() {
		$factory = $this->_mockFactory();
		$accessor = $this->_mockAccessorFactory();
		$parser = $this->_mockInterface(IEntityParser::class);
		$cache = $this->_mockInterface(IItemMutator::class);
		$injector = $this->_mockInterface(IInjector::class);
		$locator = $this->_mockInterface(ILocator::class);

		$config = [
			'coreFactory' => $factory,
			'accessorFactory' => $accessor,
			'entityParser' => $parser,
			'instanceCache' => $cache,
			'injector' => $injector,
			'locator' => $locator
		];

		$driver = $this
			->_produceFactory()
			->produce($config);

		$this->assertSame($factory, $driver->getCoreFactory());
		$this->assertSame($accessor, $driver->getAccessorFactory());
		$this->assertSame($parser, $driver->getEntityParser());
		$this->assertSame($injector, $driver->getInjector());
		$this->assertSame($locator, $driver->getLocator());
	}
}
