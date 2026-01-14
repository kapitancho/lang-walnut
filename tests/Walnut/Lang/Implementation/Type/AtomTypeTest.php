<?php

namespace Walnut\Lang\Test\Implementation\Type;

use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\UnknownType;
use Walnut\Lang\Test\BaseProgramTestHelper;

final class AtomTypeTest extends BaseProgramTestHelper {

	protected function setUp(): void {
		parent::setUp();
		$this->typeRegistryBuilder->addAtom(new TypeNameIdentifier('MyAtom'));
		$this->typeRegistryBuilder->addAtom(new TypeNameIdentifier('AnotherAtom'));
	}

	public function testAtomType(): void {
		$atomType = $this->typeRegistry->atom(new TypeNameIdentifier('MyAtom'));
		self::assertEquals('MyAtom', $atomType->name->identifier);
	}

	public function testAtomValue(): void {
		$atomType = $this->typeRegistry->atom(new TypeNameIdentifier('MyAtom'));
		self::assertEquals($atomType, $atomType->value->type);
	}

	public function testIsSubtypeOf(): void {
		self::assertTrue(
			$this->typeRegistry->atom(new TypeNameIdentifier('MyAtom'))
				->isSubtypeOf($this->typeRegistry->atom(new TypeNameIdentifier('MyAtom')))
		);
		self::assertFalse(
			$this->typeRegistry->atom(new TypeNameIdentifier('MyAtom'))
				->isSubtypeOf($this->typeRegistry->atom(new TypeNameIdentifier('AnotherAtom')))
		);
	}

	public function testAtomTypeInvalid(): void {
		$this->expectException(UnknownType::class);
		$this->typeRegistry->atom(new TypeNameIdentifier('YourAtom'));
	}
}