<?php

namespace Walnut\Lang;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry as TypeRegistryInterface;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry as ValueRegistryInterface;
use Walnut\Lang\Implementation\Program\Builder\TypeRegistryBuilder;
use Walnut\Lang\Implementation\Program\Registry\ValueRegistry;
use Walnut\Lang\Test\EmptyDependencyContainer;

final class MegaTest extends TestCase {

	private TypeRegistryInterface $typeRegistry;
	private ValueRegistryInterface $valueRegistry;

	protected function setUp(): void {
		parent::setUp();

		$this->typeRegistry = new TypeRegistryBuilder;
		$this->valueRegistry = new ValueRegistry(
			$this->typeRegistry,
			new EmptyDependencyContainer
		);
	}

	public function testNullType(): void {
		self::assertEquals(
			$this->typeRegistry->atom(new TypeNameIdentifier('Null')),
			$this->typeRegistry->null
		);
		self::assertEquals(
			$this->valueRegistry->atom(new TypeNameIdentifier('Null')),
			$this->valueRegistry->null
		);
	}

	public function testBooleanType(): void {
		self::assertEquals(
			$this->typeRegistry->enumeration(new TypeNameIdentifier('Boolean')),
			$this->typeRegistry->boolean
		);
		self::assertEquals(
			$this->typeRegistry
				->enumeration(new TypeNameIdentifier('Boolean'))
				->subsetType([
					new EnumValueIdentifier('True'),
					new EnumValueIdentifier('False')
				]),
			$this->typeRegistry->boolean
		);
		self::assertEquals(
			$this->typeRegistry
				->enumeration(new TypeNameIdentifier('Boolean'))
				->subsetType([
					new EnumValueIdentifier('True'),
				]),
			$this->typeRegistry->true
		);
		self::assertEquals(
			$this->typeRegistry
				->enumeration(new TypeNameIdentifier('Boolean'))
				->subsetType([
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
			$this->valueRegistry->integer(1),
			$this->valueRegistry->integer(2),
			$this->valueRegistry->integer(10),
		]);
		$subsetType2 = $this->typeRegistry->integerSubset([
			$this->valueRegistry->integer(1),
			$this->valueRegistry->integer(2),
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