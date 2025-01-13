<?php

namespace Walnut\Lang\Test\Implementation\Type;

use InvalidArgumentException;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Test\Implementation\BaseProgramTestHelper;

final class BooleanTypeTest extends BaseProgramTestHelper {

	public function testProperties(): void {
		$boolean = $this->typeRegistry->boolean;
		$this->assertTrue(
			$boolean->enumeration->name->equals(new TypeNameIdentifier('Boolean'))
		);
		$this->assertCount(2, $boolean->subsetValues);
		$this->assertCount(2, $boolean->values);
	}

	public function testEmptySubset(): void {
		$this->expectException(InvalidArgumentException::class);
		$this->typeRegistry->boolean->subsetType([]);
	}

	public function testInvalidSubsetValue(): void {
		$this->expectException(InvalidArgumentException::class);
		$this->typeRegistry->boolean->subsetType([new EnumValueIdentifier('Wrong')]);
	}
}