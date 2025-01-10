<?php

namespace Walnut\Lang\Blueprint\Common\Identifier;

use PHPUnit\Framework\TestCase;

final class TypeNameIdentifierTest extends TestCase {
	public function testTypeNameIdentifier(): void {
		$typeNameIdentifier1 = new TypeNameIdentifier('MyIdentifier');
		$typeNameIdentifier2 = new TypeNameIdentifier('MyIdentifier');
		$typeNameIdentifier3 = new TypeNameIdentifier('MyOtherIdentifier');
		$this->assertTrue($typeNameIdentifier1->equals($typeNameIdentifier2));
		$this->assertFalse($typeNameIdentifier1->equals($typeNameIdentifier3));
		$this->assertEquals('MyIdentifier', $typeNameIdentifier1->identifier);
		$this->assertEquals('MyIdentifier', (string)$typeNameIdentifier1);
		$this->assertEquals('"MyIdentifier"', json_encode($typeNameIdentifier1));
	}

	public function testInvalidTypeNameIdentifierSpecialChar(): void {
		$this->expectException(IdentifierException::class);
		new TypeNameIdentifier('MyIdenti@fier');
	}

	public function testInvalidTypeNameIdentifierLowercase(): void {
		$this->expectException(IdentifierException::class);
		new TypeNameIdentifier('myIdentifier');
	}
}
