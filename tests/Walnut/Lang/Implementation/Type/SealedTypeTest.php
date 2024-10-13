<?php

namespace Walnut\Lang\Implementation\Type;

use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Test\Implementation\BaseProgramTestHelper;

final class SealedTypeTest extends BaseProgramTestHelper {

	public function testProperties(): void {
		$this->programBuilder->addSealed(
			new TypeNameIdentifier('M'),
			$valueType = $this->typeRegistry->record(['a' => $this->typeRegistry->boolean()]),
			$this->expressionRegistry->constant($this->valueRegistry->null()),
			null
		);
		$type = $this->typeRegistry->sealed(new TypeNameIdentifier('M'));
		$this->assertEquals($valueType, $type->valueType());
	}

}