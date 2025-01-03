<?php

namespace Walnut\Lang\Implementation\Type;

use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Test\Implementation\BaseProgramTestHelper;

final class FalseTypeTest extends BaseProgramTestHelper {

	public function testProperties(): void {
		$false = $this->typeRegistry->false;
		$this->assertTrue(
			$false->enumeration->name->equals(new TypeNameIdentifier('Boolean'))
		);
		$this->assertCount(1, $false->subsetValues);
	}
}