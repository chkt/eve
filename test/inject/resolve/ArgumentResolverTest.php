<?php

namespace test\inject\resolve;

use PHPUnit\Framework\TestCase;

use eve\common\IFactory;
use eve\common\IAccessorFactory;
use eve\access\TraversableAccessor;
use eve\inject\IInjectable;
use eve\inject\resolve\IInjectorResolver;
use eve\inject\resolve\ArgumentResolver;



class ArgumentResolverTest
extends TestCase
{

	private function _produceAccessor(array $data) {
		return new TraversableAccessor($data);
	}

	private function _produceResolver() {
		return new ArgumentResolver();
	}


	public function testDependencyConfig() {
		$this->assertEquals([], ArgumentResolver::getDependencyConfig($this->_produceAccessor([])));
	}

	public function testInheritance() {
		$resolver = $this->_produceResolver();

		$this->assertInstanceOf(IInjectorResolver::class, $resolver);
		$this->assertInstanceOf(IInjectable::class, $resolver);
		$this->assertInstanceOf(IAccessorFactory::class, $resolver);
		$this->assertInstanceOf(IFactory::class, $resolver);
	}

	public function testProduce() {
		$resolver = $this->_produceResolver();
		$accessor = $this->_produceAccessor([ 'data' => 1 ]);

		$this->assertEquals(1, $resolver->produce($accessor));
	}

	public function testProduce_invalidAccessor() {
		$resolver = $this->_produceResolver();
		$accessor = $this->_produceAccessor([]);

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('ACC invalid key "data"');

		$resolver->produce($accessor);
	}
}
