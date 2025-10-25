<?php

namespace Walnut\Lang\Test\Implementation\Type;

use Walnut\Lang\Test\Implementation\BaseProgramTestHelper;

final class UnionTypeTest extends BaseProgramTestHelper {

	public function testIsRecordUnionTrue(): void {
		$type1 = $this->typeRegistry->union([
			$this->typeRegistry->record([
				'a' => $this->typeRegistry->integer()
			]),
			$this->typeRegistry->record([
				'b' => $this->typeRegistry->string()
			]),
		]);
		$type2 = $this->typeRegistry->record([
			'a' => $this->typeRegistry->integer(),
			'b' => $this->typeRegistry->string()
		]);
		$this->assertTrue($type1->isSubtypeOf($type2));
	}

	public function testIsRecordUnionFalse(): void {
		$type1 = $this->typeRegistry->union([
			$this->typeRegistry->record([
				'a' => $this->typeRegistry->integer()
			], $this->typeRegistry->string()),
			$this->typeRegistry->record([
				'b' => $this->typeRegistry->string()
			]),
		]);
		$type2 = $this->typeRegistry->record([
			'a' => $this->typeRegistry->integer(),
			'b' => $this->typeRegistry->string()
		], $this->typeRegistry->boolean);
		$this->assertFalse($type1->isSubtypeOf($type2));
	}

}