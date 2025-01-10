<?php

namespace Walnut\Lang\Blueprint\Common\Identifier;

use PHPUnit\Framework\TestCase;

final class EnumValueIdentifierTest extends TestCase {
	public function testEnumValueIdentifier(): void {
		$enumValueIdentifier1 = new EnumValueIdentifier('MyIdentifier');
		$enumValueIdentifier2 = new EnumValueIdentifier('MyIdentifier');
		$enumValueIdentifier3 = new EnumValueIdentifier('MyOtherIdentifier');
		$this->assertTrue($enumValueIdentifier1->equals($enumValueIdentifier2));
		$this->assertFalse($enumValueIdentifier1->equals($enumValueIdentifier3));
		$this->assertEquals('MyIdentifier', $enumValueIdentifier1->identifier);
		$this->assertEquals('MyIdentifier', (string)$enumValueIdentifier1);
		$this->assertEquals('"MyIdentifier"', json_encode($enumValueIdentifier1));
	}

	public function testInvalidEnumValueIdentifierSpecialChar(): void {
		$this->expectException(IdentifierException::class);
		new EnumValueIdentifier('MyIdenti@fier');
	}

	public function testInvalidEnumValueIdentifierLowercase(): void {
		$this->expectException(IdentifierException::class);
		new EnumValueIdentifier('myIdentifier');
	}
}
