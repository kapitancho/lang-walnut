<?php

namespace Walnut\Lang\Test\Implementation\Type;

use InvalidArgumentException;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Test\Implementation\BaseProgramTestHelper;

final class EnumerationTypeTest extends BaseProgramTestHelper {

	public function testProperties(): void {
		$this->typeRegistryBuilder->addEnumeration(new TypeNameIdentifier('Suit'), [
			new EnumValueIdentifier('Spades'),
			new EnumValueIdentifier('Hearts'),
			new EnumValueIdentifier('Diamonds'),
			new EnumValueIdentifier('Clubs')
		]);
		$type = $this->typeRegistry->complex->enumeration(new TypeNameIdentifier('Suit'));
		$this->assertTrue(
			$type->name->equals(new TypeNameIdentifier('Suit'))
		);
		$this->assertCount(4, $type->values);
		$this->assertCount(4, $type->enumeration->subsetValues);
	}

	public function testEmptySubset(): void {
		$this->expectException(InvalidArgumentException::class);
		$this->typeRegistryBuilder->addEnumeration(new TypeNameIdentifier('Suit'), [
			new EnumValueIdentifier('Spades'),
			new EnumValueIdentifier('Hearts'),
			new EnumValueIdentifier('Diamonds'),
			new EnumValueIdentifier('Clubs')
		]);
		$type = $this->typeRegistry->complex->enumeration(new TypeNameIdentifier('Suit'));
		$type->subsetType([]);
	}

	public function testInvalidSubsetValue(): void {
		$this->expectException(InvalidArgumentException::class);
		$this->typeRegistryBuilder->addEnumeration(new TypeNameIdentifier('Suit'), [
			new EnumValueIdentifier('Spades'),
			new EnumValueIdentifier('Hearts'),
			new EnumValueIdentifier('Diamonds'),
			new EnumValueIdentifier('Clubs')
		]);
		$type = $this->typeRegistry->complex->enumeration(new TypeNameIdentifier('Suit'));
		$type->subsetType([new EnumValueIdentifier('Wrong')]);
	}
}