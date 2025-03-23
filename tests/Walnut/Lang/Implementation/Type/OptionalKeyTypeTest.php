<?php

namespace Walnut\Lang\Implementation\Type;

use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Test\Implementation\BaseProgramTestHelper;

final class OptionalKeyTypeTest extends BaseProgramTestHelper {

	public function testOptionalKeyTypeIsSubtypeOf(): void {
		$type = $this->typeRegistry->optionalKey(
			$this->typeRegistry->real()
		);
		$this->assertFalse($type->isSubtypeOf(
			$this->typeRegistry->union([
				$this->typeRegistry->real(),
				$this->typeRegistry->null
			])
		));
	}

}