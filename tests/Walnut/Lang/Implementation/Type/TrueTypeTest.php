<?php

namespace Walnut\Lang\Test\Implementation\Type;

use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Test\Implementation\BaseProgramTestHelper;

final class TrueTypeTest extends BaseProgramTestHelper {

	public function testProperties(): void {
		$true = $this->typeRegistry->true;
		$this->assertTrue(
			$true->enumeration->name->equals(new TypeNameIdentifier('Boolean'))
		);
		$this->assertCount(1, $true->subsetValues);
	}
}