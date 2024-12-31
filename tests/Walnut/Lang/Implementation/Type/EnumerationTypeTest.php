<?php

namespace Walnut\Lang\Implementation\Type;

use InvalidArgumentException;
use Walnut\Lang\Blueprint\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Test\Implementation\BaseProgramTestHelper;

final class EnumerationTypeTest extends BaseProgramTestHelper {

	public function testProperties(): void {
		$this->programBuilder->addEnumeration(new TypeNameIdentifier('Suit'), [
			new EnumValueIdentifier('Spades'),
			new EnumValueIdentifier('Hearts'),
			new EnumValueIdentifier('Diamonds'),
			new EnumValueIdentifier('Clubs')
		]);
		$type = $this->typeRegistry->enumeration(new TypeNameIdentifier('Suit'));
		$this->assertTrue(
			$type->name->equals(new TypeNameIdentifier('Suit'))
		);
		$this->assertCount(4, $type->values);
		$this->assertCount(4, $type->enumeration->subsetValues);
	}

	public function testEmptySubset(): void {
		$this->expectException(InvalidArgumentException::class);
		$this->programBuilder->addEnumeration(new TypeNameIdentifier('Suit'), [
			new EnumValueIdentifier('Spades'),
			new EnumValueIdentifier('Hearts'),
			new EnumValueIdentifier('Diamonds'),
			new EnumValueIdentifier('Clubs')
		]);
		$type = $this->typeRegistry->enumeration(new TypeNameIdentifier('Suit'));
		$type->subsetType([]);
	}

	public function testInvalidSubsetValue(): void {
		$this->expectException(InvalidArgumentException::class);
		$this->programBuilder->addEnumeration(new TypeNameIdentifier('Suit'), [
			new EnumValueIdentifier('Spades'),
			new EnumValueIdentifier('Hearts'),
			new EnumValueIdentifier('Diamonds'),
			new EnumValueIdentifier('Clubs')
		]);
		$type = $this->typeRegistry->enumeration(new TypeNameIdentifier('Suit'));
		$type->subsetType([new EnumValueIdentifier('Wrong')]);
	}
}