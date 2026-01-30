<?php

namespace Walnut\Lang\Test\Almond\Engine;

use Walnut\Lang\Almond\Engine\Blueprint\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\VariableName;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Type\P\NameAndType;
use Walnut\Lang\Test\Almond\AlmondBaseTestHelper;

final class AlmondSmokeTest extends AlmondBaseTestHelper {

	public function testFunctionCall(): void {
		$fn = $this->programContext->functionValueFactory->function(
			new NameAndType($this->programContext->typeRegistry->null, null),
			new NameAndType($this->programContext->typeRegistry->nothing, null),
			$this->programContext->typeRegistry->any,
			$this->programContext->expressionRegistry->functionBody(
				$this->programContext->expressionRegistry->constant(
					$this->programContext->valueRegistry->type(
						$this->programContext->typeRegistry->any,
					)
				)
			)
		);
		$validation = $fn->validate($this->programContext->validationFactory->emptyValidationResult);
		$this->assertFalse($validation->hasErrors());
		$result = $fn->execute($this->programContext->valueRegistry->null);
		$this->assertEquals('type{Any}', (string)$result);
	}

	public function testMethodCall(): void {
		$customMethod = $this->programContext->userlandMethodBuilder->addMethod(
			new TypeName('Null'),
			new MethodName('doNothing'),
			new NameAndType($this->programContext->typeRegistry->null, new VariableName('param')),
			new NameAndType($this->programContext->typeRegistry->nothing, new VariableName('dep')),
			$this->programContext->typeRegistry->any,
			$this->programContext->expressionRegistry->functionBody(
				$this->programContext->expressionRegistry->constant(
					$this->programContext->valueRegistry->type(
						$this->programContext->typeRegistry->any,
					)
				)
			)
		);
		$validation = $customMethod->validateFunction();
		$this->assertInstanceOf(ValidationSuccess::class, $validation);

		$validation = $customMethod->validate(
			$this->programContext->typeRegistry->null,
			$this->programContext->typeRegistry->null,
		);
		$this->assertInstanceOf(ValidationSuccess::class, $validation);

		$result = $customMethod->execute(
			$this->programContext->valueRegistry->null,
			$this->programContext->valueRegistry->null
		);
		$this->assertEquals('type{Any}', (string)$result);
	}

}