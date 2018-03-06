<?php

namespace test\entity;

use PHPUnit\Framework\TestCase;

use eve\common\ITokenizer;
use eve\entity\IEntityParser;
use eve\entity\EntityParser;



final class EntityParserTest
extends TestCase
{

	public function _produceInstance() {
		return new EntityParser();
	}


	public function testInheritance() {
		$ins = $this->_produceInstance();

		$this->assertInstanceOf(IEntityParser::class, $ins);
		$this->assertInstanceOf(ITokenizer::class, $ins);
	}


	public function testParse() {
		$ins = $this->_produceInstance();

		$this->assertEquals([
			'type' => 'foo',
			'location' => 'bar'
		], $ins->parse('foo:bar'));
	}

	public function testParse_empty() {
		$ins = $this->_produceInstance();

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('ENT malformed entity ""');

		$ins->parse('');
	}

	public function testParse_noType() {
		$ins = $this->_produceInstance();

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('ENT malformed entity "bar"');

		$ins->parse('bar');
	}

	public function testParse_emptyType() {
		$ins = $this->_produceInstance();

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('ENT malformed entity ":bar"');

		$ins->parse(':bar');
	}

	public function testParse_emptyLocation() {
		$ins = $this->_produceInstance();

		$this->assertEquals([
			'type' => 'foo',
			'location' => ''
		], $ins->parse('foo:'));
	}
}
