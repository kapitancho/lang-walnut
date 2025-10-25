<?php

namespace Walnut\Lang\Test\Implementation\Type;

use Walnut\Lang\Test\Implementation\BaseProgramTestHelper;

final class IntersectionTypeTest extends BaseProgramTestHelper {

	public function testIsRecordIntersectionTrue(): void {
		$type1 = $this->typeRegistry->intersection([
			$this->typeRegistry->record([
				'a' => $this->typeRegistry->integer(),
				'b' => $this->typeRegistry->string(),
				'c' => $this->typeRegistry->boolean
			]),
			$this->typeRegistry->record([
				'a' => $this->typeRegistry->integer(),
				'b' => $this->typeRegistry->string(),
				'd' => $this->typeRegistry->any
			]),
		]);
		$type2 = $this->typeRegistry->record([
			'a' => $this->typeRegistry->integer(),
			'b' => $this->typeRegistry->string()
		]);
		$this->assertTrue($type1->isSubtypeOf($type2));
	}

	public function testIsRecordIntersectionPropertyFalse(): void {
		$type1 = $this->typeRegistry->intersection([
			$this->typeRegistry->record([
				'a' => $this->typeRegistry->integer(),
				'b' => $this->typeRegistry->string(),
				'c' => $this->typeRegistry->boolean
			], $this->typeRegistry->null),
			$this->typeRegistry->record([
				'a' => $this->typeRegistry->integer(),
				'b' => $this->typeRegistry->string(),
				'd' => $this->typeRegistry->any
			], $this->typeRegistry->null),
		]);
		$type2 = $this->typeRegistry->record([
			'a' => $this->typeRegistry->integer(),
			'b' => $this->typeRegistry->string()
		], $this->typeRegistry->boolean);
		$this->assertFalse($type1->isSubtypeOf($type2));
	}

	public function testIsRecordIntersectionFalse(): void {
		$type1 = $this->typeRegistry->intersection([
			$this->typeRegistry->record([
				'a' => $this->typeRegistry->integer(),
				'b' => $this->typeRegistry->string(),
				'c' => $this->typeRegistry->boolean
			], $this->typeRegistry->null),
			$this->typeRegistry->record([
				'a' => $this->typeRegistry->integer(),
				'b' => $this->typeRegistry->string(),
				'd' => $this->typeRegistry->boolean
			], $this->typeRegistry->null),
		]);
		$type2 = $this->typeRegistry->record([
			'a' => $this->typeRegistry->integer(),
			'b' => $this->typeRegistry->string()
		], $this->typeRegistry->boolean);
		$this->assertFalse($type1->isSubtypeOf($type2));
	}

}