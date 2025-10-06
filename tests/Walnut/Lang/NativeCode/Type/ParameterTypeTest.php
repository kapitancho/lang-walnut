<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ParameterTypeTest extends CodeExecutionTestHelper {

	public function testParameterType(): void {
		$result = $this->executeCodeSnippet("type{^String => Real}->parameterType;");
		$this->assertEquals("type{String}", $result);
	}

	public function testParameterTypeMetaType(): void {
		$result = $this->executeCodeSnippet("getParameterType(type{^String => Real});",
			valueDeclarations: "getParameterType = ^Type<Function> => Type :: #->parameterType;");
		$this->assertEquals("type{String}", $result);
	}

	public function testParameterTypeInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type',
			"type{String}->parameterType;");
	}

}