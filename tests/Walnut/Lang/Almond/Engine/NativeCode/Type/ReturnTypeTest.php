<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class ReturnTypeTest extends CodeExecutionTestHelper {

	public function testReturnTypeResult(): void {
		$result = $this->executeCodeSnippet("type{Result<String, Real>}->returnType;");
		$this->assertEquals("type{String}", $result);
	}

	public function testReturnTypeResultMetaType(): void {
		$result = $this->executeCodeSnippet("getReturnType(type{Result<String, Real>});",
			valueDeclarations: "getReturnType = ^Type<Result> => Type :: #->returnType;");
		$this->assertEquals("type{String}", $result);
	}

	public function testReturnTypeFunction(): void {
		$result = $this->executeCodeSnippet("type{^Real => String}->returnType;");
		$this->assertEquals("type{String}", $result);
	}

	public function testReturnTypeFunctionMetaType(): void {
		$result = $this->executeCodeSnippet("getReturnType(type{^Real => String});",
			valueDeclarations: "getReturnType = ^Type<Function> => Type :: #->returnType;");
		$this->assertEquals("type{String}", $result);
	}

	public function testReturnTypeInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type',
			"type{String}->returnType;");
	}

}