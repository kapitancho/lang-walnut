<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class WithReturnTypeTest extends CodeExecutionTestHelper {

	public function testWithReturnTypeResult(): void {
		$result = $this->executeCodeSnippet("type{Result<String, Real>}->withReturnType(type{Boolean});");
		$this->assertEquals("type{Result<Boolean, Real>}", $result);
	}

	public function testWithReturnTypeResultMetaType(): void {
		$result = $this->executeCodeSnippet("getWithReturnType(type{Result<String, Real>});",
			"getWithReturnType = ^Type<Result> => Type<Result> :: #->withReturnType(type{Boolean});");
		$this->assertEquals("type{Result<Boolean, Real>}", $result);
	}

	public function testWithReturnTypeFunction(): void {
		$result = $this->executeCodeSnippet("type{^Real => String}->withReturnType(type{Boolean});");
		$this->assertEquals("type{^Real => Boolean}", $result);
	}

	public function testWithReturnTypeFunctionMetaType(): void {
		$result = $this->executeCodeSnippet("getWithReturnType(type{^Real => String});",
			"getWithReturnType = ^Type<Function> => Type<^Nothing => Boolean> :: #->withReturnType(type{Boolean});");
		$this->assertEquals("type{^Real => Boolean}", $result);
	}

	public function testWithReturnTypeInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type',
			"type{String}->withReturnType(type{Boolean});");
	}

	public function testWithReturnTypeResultInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type',
			"type{Result<Boolean, Real>}->withReturnType(42);");
	}

	public function testWithReturnTypeFunctionInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type',
			"type{^Real => Boolean}->withReturnType(42);");
	}

}