<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class ErrorTypeTest extends CodeExecutionTestHelper {

	public function testErrorType(): void {
		$result = $this->executeCodeSnippet("type{Result<Real, String>}->errorType;");
		$this->assertEquals("type{String}", $result);
	}

	public function testErrorTypeMetaType(): void {
		$result = $this->executeCodeSnippet("getErrorType(type{Result<Real, String>});",
			valueDeclarations: "getErrorType = ^Type<Result> => Type :: #->errorType;");
		$this->assertEquals("type{String}", $result);
	}

	public function testErrorTypeInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type',
			"type{String}->errorType;");
	}

}