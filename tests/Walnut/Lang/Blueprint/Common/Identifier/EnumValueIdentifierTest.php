<?php

namespace Walnut\Lang\Test\Blueprint\Common\Identifier;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\IdentifierException;

final class EnumValueIdentifierTest extends TestCase {
	public function testEnumValueIdentifier(): void {
		$enumValueIdentifier1 = new EnumValueIdentifier('MyIdentifier');
		$enumValueIdentifier2 = new EnumValueIdentifier('MyIdentifier');
		$enumValueIdentifier3 = new EnumValueIdentifier('MyOtherIdentifier');
		$enumValueIdentifier4 = new EnumValueIdentifier('myLowercaseIdentifier_');
		$this->assertTrue($enumValueIdentifier1->equals($enumValueIdentifier2));
		$this->assertFalse($enumValueIdentifier1->equals($enumValueIdentifier3));
		$this->assertEquals('MyIdentifier', $enumValueIdentifier1->identifier);
		$this->assertEquals('MyIdentifier', (string)$enumValueIdentifier1);
		$this->assertEquals('"MyIdentifier"', json_encode($enumValueIdentifier1));
		$this->assertEquals('"myLowercaseIdentifier_"', json_encode($enumValueIdentifier4));
	}

	public function testInvalidEnumValueIdentifierSpecialChar(): void {
		$this->expectException(IdentifierException::class);
		new EnumValueIdentifier('MyIdenti@fier');
	}
}
