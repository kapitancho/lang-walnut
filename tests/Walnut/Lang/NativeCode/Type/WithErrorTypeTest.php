<?php

namespace Walnut\Lang\Test\NativeCode\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class WithErrorTypeTest extends CodeExecutionTestHelper {

	public function testWithErrorType(): void {
		$result = $this->executeCodeSnippet("type{Result<Real, String>}->withErrorType(type{Integer});");
		$this->assertEquals("type{Result<Real, Integer>}", $result);
	}

	public function testWithErrorTypeMetaType(): void {
		$result = $this->executeCodeSnippet("getWithErrorType(type{Result<Real, String>});",
			valueDeclarations: "getWithErrorType = ^Type<Result> => Type :: #->withErrorType(type{Integer});");
		$this->assertEquals("type{Result<Real, Integer>}", $result);
	}

	public function testWithErrorTypeInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type',
			"type{String}->withErrorType(type{Integer});");
	}

	public function testWithErrorTypeInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type',
			"type{Result<Real, String>}->withErrorType(1)");
	}

}