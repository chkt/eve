<?php

namespace test\driver;

use PHPUnit\Framework\TestCase;

use eve\common\ISimpleFactory;
use eve\common\IDriver;
use eve\factory\IFactory;
use eve\access\IItemAccessor;
use eve\access\TraversableAccessor;
use eve\access\IItemMutator;
use eve\entity\IEntityParser;
use eve\driver\IInjectorHost;
use eve\driver\IInjectorDriver;
use eve\driver\InjectorDriver;
use eve\inject\IInjector;
use eve\provide\ILocator;



final class InjectorDriverTest
extends TestCase
{

	private function _produceDriver(array& $deps = []) : InjectorDriver {
		return new InjectorDriver($deps);
	}


	public function testInheritance() {
		$driver = $this->_produceDriver();

		$this->assertInstanceOf(TraversableAccessor::class, $driver);
		$this->assertInstanceOf(IInjectorDriver::class, $driver);
		$this->assertInstanceOf(IDriver::class, $driver);
		$this->assertInstanceOf(IItemAccessor::class, $driver);
	}

	public function testGetHost() {
		$host = $this->getMockBuilder(IInjectorHost::class)->getMock();
		$deps = [ 'host' => $host];

		$driver = $this->_produceDriver($deps);

		$this->assertSame($host, $driver->getHost());
	}

	public function testGetInjector() {
		$injector = $this->getMockBuilder(IInjector::class)->getMock();
		$deps = [ 'injector' => $injector ];

		$driver = $this->_produceDriver($deps);

		$this->assertSame($injector, $driver->getInjector());
	}

	public function testGetLocator() {
		$locator = $this->getMockBuilder(ILocator::class)->getMock();
		$deps = [ 'locator' => $locator ];

		$driver = $this->_produceDriver($deps);

		$this->assertSame($locator, $driver->getLocator());
	}

	public function testGetReferences() {
		$refs = $this->getMockBuilder(IItemAccessor::class)->getMock();
		$deps = [ 'references' => $refs];

		$driver = $this->_produceDriver($deps);

		$this->assertSame($refs, $driver->getReferences());
	}

	public function testGetEntityParser() {
		$parser = $this->getMockBuilder(IEntityParser::class)->getMock();
		$deps = [ 'entityParser' => $parser];

		$driver = $this->_produceDriver($deps);

		$this->assertSame($parser, $driver->getEntityParser());
	}

	public function testGetFactory() {
		$fab = $this->getMockBuilder(IFactory::class)->getMock();
		$deps = [ 'factory' => $fab ];

		$driver = $this->_produceDriver($deps);

		$this->assertSame($fab, $driver->getFactory());
	}

	public function testGetAccessorFactory() {
		$fab = $this->getMockBuilder(ISimpleFactory::class)->getMock();
		$deps = [ 'accessorFactory' => $fab];

		$driver = $this->_produceDriver($deps);

		$this->assertSame($fab, $driver->getAccessorFactory());
	}

	public function testGetInstanceCache() {
		$cache = $this->getMockBuilder(IItemMutator::class)->getMock();
		$deps = [ 'instanceCache' => $cache ];

		$driver = $this->_produceDriver($deps);

		$this->assertSame($cache, $driver->getInstanceCache());
	}
}
