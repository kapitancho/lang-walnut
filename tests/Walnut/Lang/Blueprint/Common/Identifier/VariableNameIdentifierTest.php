<?php

namespace Walnut\Lang\Test\Blueprint\Common\Identifier;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Blueprint\Common\Identifier\IdentifierException;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;

final class VariableNameIdentifierTest extends TestCase {
	public function testVariableNameIdentifier(): void {
		$variableNameIdentifier1 = new VariableNameIdentifier('myIdentifier');
		$variableNameIdentifier2 = new VariableNameIdentifier('myIdentifier');
		$variableNameIdentifier3 = new VariableNameIdentifier('myOtherIdentifier');
		$this->assertTrue($variableNameIdentifier1->equals($variableNameIdentifier2));
		$this->assertFalse($variableNameIdentifier1->equals($variableNameIdentifier3));
		$this->assertEquals('myIdentifier', $variableNameIdentifier1->identifier);
		$this->assertEquals('myIdentifier', (string)$variableNameIdentifier1);
		$this->assertEquals('"myIdentifier"', json_encode($variableNameIdentifier1));
	}

	public function testInvalidVariableNameIdentifierSpecialChar(): void {
		$this->expectException(IdentifierException::class);
		new VariableNameIdentifier('myIdenti@fier');
	}

	public function testInvalidVariableNameIdentifierUppercase(): void {
		$this->expectException(IdentifierException::class);
		new VariableNameIdentifier('MyIdentifier');
	}
}
