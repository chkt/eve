<?php

namespace test\driver;

use PHPUnit\Framework\TestCase;

use eve\common\IHost;
use eve\access\ItemAccessor;
use eve\driver\IInjectorDriver;
use eve\driver\IInjectorHost;
use eve\driver\InjectorHost;
use eve\inject\IInjector;
use eve\provide\ILocator;



class InjectorHostTest
extends TestCase
{

	private function _mockInjector() {
		return $this
			->getMockBuilder(IInjector::class)
			->getMock();
	}

	private function _mockLocator() {
		return $this
			->getMockBuilder(ILocator::class)
			->getMock();
	}

	private function _mockDriver(IInjector $injector = null, ILocator $locator = null) : IInjectorDriver {
		if (is_null($injector)) $injector = $this->_mockInjector();
		if (is_null($locator)) $locator = $this->_mockLocator();

		$ins = $this
			->getMockBuilder(IInjectorDriver::class)
			->getMock();

		$ins
			->expects($this->once())
			->method('getInjector')
			->with()
			->willReturn($injector);

		$ins
			->expects($this->once())
			->method('getLocator')
			->with()
			->willReturn($locator);

		return $ins;
	}


	private function _produceHost(IInjectorDriver $driver = null) : InjectorHost {
		if (is_null($driver)) $driver = $this->_mockDriver();

		return new InjectorHost($driver);
	}


	public function testInheritance() {
		$host = $this->_produceHost();

		$this->assertInstanceOf(IInjectorHost::class, $host);
		$this->assertInstanceOf(IHost::class, $host);
		$this->assertInstanceOf(ItemAccessor::class, $host);
	}


	public function testGetInjector() {
		$host = $this->_produceHost();
		$injector = $host->getInjector();

		$this->assertInstanceOf(IInjector::class, $injector);
		$this->assertSame($injector, $host->getInjector());
	}

	public function testGetLocator() {
		$host = $this->_produceHost();
		$locator = $host->getLocator();

		$this->assertInstanceOf(ILocator::class, $locator);
		$this->assertSame($locator, $host->getLocator());
	}
}
