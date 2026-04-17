<?php

namespace Walnut\Lang\Test\Almond\Engine\Implementation\Code\Type\BuiltIn;

use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\EnumerationSubsetType;
use Walnut\Lang\Test\Almond\AlmondBaseTestHelper;

final class EnumerationSubsetTypeTest extends AlmondBaseTestHelper {

	public function testNoValues(): void {
		$this->expectExceptionMessage('Cannot create an empty subset type');
		new EnumerationSubsetType(
			$this->programContext->typeRegistry->boolean,
			[]
		);
	}

	public function testDuplicateValue(): void {
		$this->expectExceptionMessage('The value "true" is already in the subset type');
		new EnumerationSubsetType(
			$this->programContext->typeRegistry->boolean,
			[
				$this->programContext->valueRegistry->true,
				$this->programContext->valueRegistry->true
			]
		);
	}

	public function testWrongValueType(): void {
		$this->expectExceptionMessage('Invalid argument: expected type "EnumerationValue", got "int"');
		/** @noinspection PhpParamsInspection */
		new EnumerationSubsetType(
			$this->programContext->typeRegistry->boolean,
			[7]
		);
	}

}