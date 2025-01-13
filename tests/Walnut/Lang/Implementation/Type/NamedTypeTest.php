<?php

namespace Walnut\Lang\Test\Implementation\Type;

use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Test\Implementation\BaseProgramTestHelper;

final class NamedTypeTest extends BaseProgramTestHelper {

	public function testProperties(): void {
		$type = $this->typeRegistry->alias(new TypeNameIdentifier('JsonValue'));
		$this->assertEquals('JsonValue', (string)$type);
		$this->assertTrue($type->isSupertypeOf($this->typeRegistry->nothing));
	}

}