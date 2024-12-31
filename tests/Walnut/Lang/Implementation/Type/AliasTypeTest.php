<?php

namespace Walnut\Lang\Implementation\Type;

use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Test\Implementation\BaseProgramTestHelper;

final class AliasTypeTest extends BaseProgramTestHelper {

	public function testProperties(): void {
		$this->programBuilder->addAlias(new TypeNameIdentifier('M'), $boolean = $this->typeRegistry->boolean);
		$type = $this->typeRegistry->alias(new TypeNameIdentifier('M'));
		$this->assertEquals($boolean, $type->aliasedType);
	}

}