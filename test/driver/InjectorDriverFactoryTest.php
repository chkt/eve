<?php

namespace test\driver;

use PHPUnit\Framework\TestCase;

use eve\common\factory\ISimpleFactory;
use eve\common\factory\ICoreFactory;
use eve\common\factory\ASimpleFactory;
use eve\common\access\IItemMutator;
use eve\common\access\TraversableAccessor;
use eve\entity\IEntityParser;
use eve\inject\IInjector;
use eve\inject\cache\IKeyEncoder;
use eve\provide\ILocator;
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


	private function _mockBaseFactory() {
		$ins = $this
			->getMockBuilder(ICoreFactory::class)
			->getMock();

		$ins
			->method('newInstance')
			->with(
				$this->isType('string'),
				$this->logicalOr(
					$this->isType('array'),
					$this->isNull()
				)
			)
			->willReturnCallback(function(string $qname, array $args = []) {
				return $this->_buildDependency($qname, $args);
			});

		return $ins;
	}

	private function _mockAccessorFactory() : ISimpleFactory {
		$ins = $this
			->getMockBuilder(ISimpleFactory::class)
			->getMock();

		$ins
			->method('produce')
			->with($this->isType('array'))
			->willReturnCallback(function(array& $data) {
				return new TraversableAccessor($data);
			});

		return $ins;
	}

	private function _mockDriver(array $args) {
		$ins = $this->_mockInterface(IInjectorDriver::class, $args);

		$ins
			->method('getCoreFactory')
			->willReturnCallback(function() use ($args) {
				return $args[0]['coreFactory'];
			});

		$ins
			->method('getInjector')
			->willReturnCallback(function() use ($args) {
				return $args[0]['injector'];
			});

		return $ins;
	}

	private function _mockInjector($args) {
		$ins = $this->_mockInterface(IInjector::class, $args);

		$ins
			->method('produce')
			->with(
				$this->isType('string'),
				$this->logicalOr(
					$this->isType('array'),
					$this->isNull()
				)
			)
			->willReturnCallback(function(string $qname, array $args = []) {
				return $this->_buildDependency($qname, $args);
			});

		return $ins;
	}


	private function _buildDependency(string $qname, array $args) {
		$map = [
			\eve\common\access\TraversableAccessorFactory::class => \eve\common\factory\ISimpleFactory::class,
			\eve\common\access\TraversableMutator::class => \eve\common\access\IItemMutator::class,
			\eve\driver\InjectorDriver::class => \eve\driver\IInjectorDriver::class,
			\eve\inject\cache\KeyEncoder::class => \eve\inject\cache\IKeyEncoder::class,
			\eve\inject\IdentityInjector::class => \eve\inject\IInjector::class,
			\eve\entity\EntityParser::class => \eve\entity\IEntityParser::class,
			\eve\provide\ProviderProvider::class => \eve\provide\ILocator::class
		];

		$this->assertArrayHasKey($qname, $map);

		$iname = $map[$qname];

		switch ($iname) {
			case \eve\common\factory\ISimpleFactory::class : return $this->_mockAccessorFactory($args);
			case \eve\driver\IInjectorDriver::class : return $this->_mockDriver($args);
			case \eve\inject\IInjector::class : return $this->_mockInjector($args);
			default : return $this->_mockInterface($iname, $args);
		}
	}


	private function _produceDriverFactory(ICoreFactory $base = null) {
		if (is_null($base)) $base = $this->_mockInterface(ICoreFactory::class);

		return new InjectorDriverFactory($base);
	}


	public function testInheritance() {
		$fab = $this->_produceDriverFactory();

		$this->assertInstanceOf(ASimpleFactory::class, $fab);
	}

	public function testProduce() {
		$base = $this->_mockBaseFactory();
		$driverFactory = $this->_produceDriverFactory($base);

		$config = [
			'resolvers' => [
				IInjector::TYPE_INJECTOR => 'foo',
				IInjector::TYPE_LOCATOR => 'bar',
				IInjector::TYPE_ARGUMENT => 'baz',
				IInjector::TYPE_FACTORY => 'quux'
			],
			'providers' => [
				'foo' => 'bar',
				'baz' => 'quux'
			]
		];

		$driver = $driverFactory->produce($config);

		$this->assertInstanceOf(IInjectorDriver::class, $driver);
		$this->assertInstanceOf(ICoreFactory::class, $driver->p0['coreFactory']);
		$this->assertInstanceOf(ISimpleFactory::class, $driver->p0['accessorFactory']);
		$this->assertInstanceOf(IKeyEncoder::class, $driver->p0['keyEncoder']);
		$this->assertSame($driver->p0['coreFactory'], $driver->p0['keyEncoder']->p0);
		$this->assertInstanceOf(IItemMutator::class, $driver->p0['instanceCache']);
		$this->assertInternalType('array', $driver->p0['instanceCache']->p0);
		$this->assertInstanceOf(IInjector::class, $driver->p0['injector']);
		$this->assertSame($driver, $driver->p0['injector']->p0);
		$this->assertSame($config['resolvers'], $driver->p0['injector']->p1);
		$this->assertInstanceOf(IEntityParser::class, $driver->p0['entityParser']);
		$this->assertInstanceOf(ILocator::class, $driver->p0['locator']);
		$this->assertSame($driver, $driver->p0['locator']->driver);
		$this->assertSame($config['providers'], $driver->p0['locator']->providerNames);
	}
}
