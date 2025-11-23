<?php

namespace Walnut\Lang\Test\Implementation\Type;

use Walnut\Lang\Test\Implementation\BaseProgramTestHelper;

final class TypeTypeTest extends BaseProgramTestHelper {

	public function testMutable(): void {
		$type1 = $this->typeRegistry->type(
			$this->typeRegistry->mutable(
				$this->typeRegistry->boolean
			)
		);
		$type2 = $this->typeRegistry->type(
			$this->typeRegistry->mutable(
				$this->typeRegistry->boolean
			)
		);
		$this->assertTrue(
			$type1->isSubtypeOf($type2)
		);
	}

	public function testMutableSub(): void {
		$type1 = $this->typeRegistry->type(
			$this->typeRegistry->mutable(
				$this->typeRegistry->true
			)
		);
		$type2 = $this->typeRegistry->type(
			$this->typeRegistry->mutable(
				$this->typeRegistry->boolean
			)
		);
		$this->assertTrue(
			$type1->isSubtypeOf($type2)
		);
	}

	public function testMutableSup(): void {
		$type1 = $this->typeRegistry->type(
			$this->typeRegistry->mutable(
				$this->typeRegistry->boolean
			)
		);
		$type2 = $this->typeRegistry->type(
			$this->typeRegistry->mutable(
				$this->typeRegistry->true
			)
		);
		$this->assertFalse(
			$type1->isSubtypeOf($type2)
		);
	}
}