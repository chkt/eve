<?php

namespace test\driver;

use PHPUnit\Framework\TestCase;

use eve\common\IHost;
use eve\common\IDriver;
use eve\common\access\IItemAccessor;
use eve\common\access\AccessorException;
use eve\common\assembly\IAssemblyHost;
use eve\driver\IInjectorHost;
use eve\driver\IInjectorDriver;
use eve\driver\InjectorDriverAssembly;
use eve\driver\InjectorDriver;



final class InjectorDriverTest
extends TestCase
{

	private function _mockAssembly() {
		$assembly = $this
			->getMockBuilder(IAssemblyHost::class)
			->getMock();

		$assembly
			->method('getItem')
			->with($this->isType('string'))
			->willReturnCallback(function($key) {
				$map = [
					'coreFactory' => \eve\common\factory\ICoreFactory::class,
					'accessorFactory' => \eve\common\factory\ISimpleFactory::class,
					'keyEncoder' => \eve\inject\cache\IKeyEncoder::class,
					'instanceCache' => \eve\common\access\IItemMutator::class,
					'injector' => \eve\inject\IInjector::class,
					'entityParser' => \eve\entity\IEntityParser::class,
					'locator' => \eve\provide\ILocator::class
				];

				$this->assertArrayHasKey($key, $map);

				return $this
					->getMockBuilder($map[$key])
					->getMock();
			});

		return $assembly;
	}


	private function _produceDriver(InjectorDriverAssembly $assembly = null) : InjectorDriver {
		if (is_null($assembly)) $assembly = $this->_mockAssembly();

		return new InjectorDriver($assembly);
	}


	public function testInheritance() {
		$driver = $this->_produceDriver();

		$this->assertInstanceOf(IInjectorDriver::class, $driver);
		$this->assertInstanceOf(IDriver::class, $driver);
		$this->assertInstanceOf(IInjectorHost::class, $driver);
		$this->assertInstanceOf(IHost::class, $driver);
		$this->assertInstanceOf(IItemAccessor::class, $driver);
	}


	public function testHasKey() {
		$driver = $this->_produceDriver();

		$this->assertTrue($driver->hasKey($driver::ITEM_CORE_FACTORY));
		$this->assertTrue($driver->hasKey($driver::ITEM_ACCESSOR_FACTORY));
		$this->assertTrue($driver->hasKey($driver::ITEM_KEY_ENCODER));
		$this->assertTrue($driver->hasKey($driver::ITEM_INSTANCE_CACHE));
		$this->assertTrue($driver->hasKey($driver::ITEM_INJECTOR));
		$this->assertTrue($driver->hasKey($driver::ITEM_ENTITY_PARSER));
		$this->assertTrue($driver->hasKey($driver::ITEM_LOCATOR));
		$this->assertFalse($driver->hasKey('foo'));
		$this->assertFalse($driver->hasKey('resolverAssembly'));
		$this->assertFalse($driver->hasKey('providerAssembly'));
	}


	public function testGetItem() {
		$driver = $this->_produceDriver();

		$this->assertInstanceOf(\eve\common\factory\ICoreFactory::class, $driver->getItem($driver::ITEM_CORE_FACTORY));
		$this->assertInstanceOf(\eve\common\factory\ISimpleFactory::class, $driver->getItem($driver::ITEM_ACCESSOR_FACTORY));
		$this->assertInstanceOf(\eve\inject\cache\IKeyEncoder::class, $driver->getItem($driver::ITEM_KEY_ENCODER));
		$this->assertInstanceOf(\eve\common\access\IItemMutator::class, $driver->getItem($driver::ITEM_INSTANCE_CACHE));
		$this->assertInstanceOf(\eve\inject\IInjector::class, $driver->getItem($driver::ITEM_INJECTOR));
		$this->assertInstanceOf(\eve\entity\IEntityParser::class, $driver->getItem($driver::ITEM_ENTITY_PARSER));
		$this->assertInstanceOf(\eve\provide\ILocator::class, $driver->getItem($driver::ITEM_LOCATOR));
	}

	public function testGetItem_invalidKey() {
		$driver = $this->_produceDriver();

		$this->expectException(AccessorException::class);
		$this->expectExceptionMessage('ACC invalid key "foo"');

		$driver->getItem('foo');
	}


	public function testGetInjector() {
		$driver = $this->_produceDriver();

		$this->assertInstanceOf(\eve\inject\IInjector::class, $driver->getInjector());
	}

	public function testGetLocator() {
		$driver = $this->_produceDriver();

		$this->assertInstanceOf(\eve\provide\ILocator::class, $driver->getLocator());
	}

	public function testGetEntityParser() {
		$driver = $this->_produceDriver();

		$this->assertInstanceOf(\eve\entity\IEntityParser::class, $driver->getEntityParser());
	}

	public function testGetBaseFactory() {
		$driver = $this->_produceDriver();

		$this->assertInstanceOf(\eve\common\factory\ICoreFactory::class, $driver->getCoreFactory());
	}

	public function testGetAccessorFactory() {
		$driver = $this->_produceDriver();

		$this->assertInstanceOf(\eve\common\factory\ISimpleFactory::class, $driver->getAccessorFactory());
	}

	public function testGetKeyEncoder() {
		$driver = $this->_produceDriver();

		$this->assertInstanceOf(\eve\inject\cache\IKeyEncoder::class, $driver->getKeyEncoder());
	}

	public function testGetInstanceCache() {
		$driver = $this->_produceDriver();

		$this->assertInstanceOf(\eve\common\access\IItemMutator::class, $driver->getInstanceCache());
	}
}
