<?php

namespace test\driver;

use PHPUnit\Framework\TestCase;

use eve\common\factory\ISimpleFactory;
use eve\common\factory\ICoreFactory;
use eve\common\factory\ASimpleFactory;
use eve\common\access\ITraversableAccessor;
use eve\common\assembly\IAssemblyHost;
use eve\driver\IInjectorDriver;
use eve\driver\InjectorDriverFactory;



final class InjectorDriverFactoryTest
extends TestCase
{

	private function _mockInterface(string $qname, array $args = []) {
		$ins = $this
			->getMockBuilder($qname)
			->getMock();

		foreach ($args as $key => & $value) {
			$prop = (is_numeric($key) ? 'p' : '') . $key;

			$ins->$prop =& $value;
		}

		return $ins;
	}

	private function _mockAccessorFactory(array $args) {
		$access = $this->_mockInterface(ISimpleFactory::class, $args);

		$access
			->method('produce')
			->with($this->isType('array'))
			->willReturnCallback(function(array& $data) {
				return $this->_mockInterface(ITraversableAccessor::class, [ & $data ]);
			});

		return $access;
	}

	private function _mockDriverAssembly(array $args) {
		$assembly = $this->_mockInterface(IAssemblyHost::class, $args);

		$assembly
			->method('getItem')
			->with($this->equalTo('coreFactory'))
			->willReturn($assembly->p0);

		return $assembly;
	}


	private function _mockBaseFactory() {
		$base = $this
			->getMockBuilder(ICoreFactory::class)
			->getMock();

		$base
			->method('newInstance')
			->with(
				$this->isType('string'),
				$this->logicalOr(
					$this->isType('array'),
					$this->isNull()
				)
			)
			->willReturnCallback(function(string $qname, array $args = []) {
				if ($qname === \eve\common\access\TraversableAccessorFactory::class) return $this->_mockAccessorFactory($args);
				else if ($qname === \eve\driver\InjectorDriverAssembly::class) return $this->_mockDriverAssembly($args);
				else if ($qname === \eve\driver\InjectorDriver::class) return $this->_mockInterface(IInjectorDriver::class, $args);
				else $this->assertFalse(true);
			});

		return $base;
	}


	private function _produceDriverFactory(ICoreFactory $base = null) {
		if (is_null($base)) $base = $this->_mockBaseFactory();

		return new InjectorDriverFactory($base);
	}


	public function testInheritance() {
		$fab = $this->_produceDriverFactory();

		$this->assertInstanceOf(ASimpleFactory::class, $fab);
	}

	public function testProduce() {
		$fab = $this->_produceDriverFactory();
		$driver = $fab->produce();

		$this->assertInstanceOf(IInjectorDriver::class, $driver);
		$this->assertObjectHasAttribute('p0', $driver);
		$this->assertInstanceOf(IAssemblyHost::class, $driver->p0);
		$this->assertObjectHasAttribute('p0', $driver->p0);
		$this->assertInstanceOf(ICoreFactory::class, $driver->p0->p0);
		$this->assertObjectHasAttribute('p1', $driver->p0);
		$this->assertInstanceOf(ISimpleFactory::class, $driver->p0->p1);
		$this->assertObjectHasAttribute('p2', $driver->p0);
		$this->assertInstanceOf(ITraversableAccessor::class, $driver->p0->p2);
		$this->assertObjectHasAttribute('p0', $driver->p0->p2);
		$this->assertInternalType('array', $driver->p0->p2->p0);
		$this->assertArrayHasKey('resolvers', $driver->p0->p2->p0);
		$this->assertEquals([
			'injector' => \eve\inject\resolve\HostResolver::class,
			'locator' => \eve\inject\resolve\HostResolver::class,
			'argument' => \eve\inject\resolve\ArgumentResolver::class,
			'factory' => \eve\inject\resolve\FactoryResolver::class
		], $driver->p0->p2->p0['resolvers']);
		$this->assertArrayHasKey('providers', $driver->p0->p2->p0);
		$this->assertEquals([], $driver->p0->p2->p0['providers']);
	}
}
