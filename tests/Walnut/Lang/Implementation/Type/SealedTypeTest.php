<?php

namespace Walnut\Lang\Test\Implementation\Type;

use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Test\Implementation\BaseProgramTestHelper;

final class SealedTypeTest extends BaseProgramTestHelper {

	public function testProperties(): void {
		$this->typeRegistryBuilder->addSealed(
			new TypeNameIdentifier('M'),
			$valueType = $this->typeRegistry->record(['a' => $this->typeRegistry->boolean]),
			$this->expressionRegistry->functionBody(
				$this->expressionRegistry->constant($this->valueRegistry->null)
			),
			null
		);
		$type = $this->typeRegistry->complex->sealed(new TypeNameIdentifier('M'));
		$this->assertEquals($valueType, $type->valueType);
	}

}