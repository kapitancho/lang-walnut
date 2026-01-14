<?php

namespace Walnut\Lang\Test;

use BcMath\Number;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;

final class MegaTest extends BaseProgramTestHelper {

	public function testNullType(): void {
		self::assertEquals(
			$this->typeRegistry->complex->atom(new TypeNameIdentifier('Null')),
			$this->typeRegistry->null
		);
		self::assertEquals(
			$this->valueRegistry->atom(new TypeNameIdentifier('Null')),
			$this->valueRegistry->null
		);
	}

	public function testBooleanType(): void {
		self::assertEquals(
			$this->typeRegistry->complex->enumeration(new TypeNameIdentifier('Boolean')),
			$this->typeRegistry->boolean
		);
		self::assertEquals(
			$this->typeRegistry
				->complex->enumerationSubsetType(new TypeNameIdentifier('Boolean'), [
					new EnumValueIdentifier('True'),
					new EnumValueIdentifier('False')
				]),
			$this->typeRegistry->boolean
		);
		self::assertEquals(
			$this->typeRegistry
				->complex->enumerationSubsetType(new TypeNameIdentifier('Boolean'), [
					new EnumValueIdentifier('True'),
				]),
			$this->typeRegistry->true
		);
		self::assertEquals(
			$this->typeRegistry
				->complex->enumerationSubsetType(new TypeNameIdentifier('Boolean'), [
					new EnumValueIdentifier('False'),
				]),
			$this->typeRegistry->false
		);
		self::assertEquals(
			$this->valueRegistry->enumerationValue(
				new TypeNameIdentifier('Boolean'),
				new EnumValueIdentifier('True'),
			),
			$this->valueRegistry->true
		);
		self::assertEquals(
			$this->valueRegistry->enumerationValue(
				new TypeNameIdentifier('Boolean'),
				new EnumValueIdentifier('False'),
			),
			$this->valueRegistry->false
		);
		$this->assertTrue(
			$this->typeRegistry->true->isSubtypeOf(
				$this->typeRegistry->boolean
			)
		);
		$this->assertFalse(
			$this->typeRegistry->boolean->isSubtypeOf(
				$this->typeRegistry->true
			)
		);
		$this->assertTrue(
			$this->typeRegistry->false->isSubtypeOf(
				$this->typeRegistry->boolean
			)
		);
		$this->assertFalse(
			$this->typeRegistry->boolean->isSubtypeOf(
				$this->typeRegistry->false
			)
		);
	}

	public function testIntegerType(): void {
		$subsetType1 = $this->typeRegistry->integerSubset([
			new Number(1),
			new Number(2),
			new Number(10),
		]);
		$subsetType2 = $this->typeRegistry->integerSubset([
			new Number(1),
			new Number(2),
		]);
		$rangeType = $this->typeRegistry->integer(-5, 5);
		$integerType = $this->typeRegistry->integer();
		self::assertFalse($subsetType1->isSubtypeOf($subsetType2));
		self::assertTrue($subsetType2->isSubtypeOf($subsetType1));
		self::assertFalse($subsetType1->isSubtypeOf($rangeType));
		self::assertTrue($subsetType2->isSubtypeOf($rangeType));
		self::assertTrue($subsetType1->isSubtypeOf($integerType));
		self::assertTrue($subsetType2->isSubtypeOf($integerType));
		self::assertTrue($rangeType->isSubtypeOf($integerType));
		self::assertFalse($integerType->isSubtypeOf($rangeType));
	}

}