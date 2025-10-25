<?php

namespace Walnut\Lang\Test\NativeCode\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class WithParameterTypeTest extends CodeExecutionTestHelper {

	public function testWithParameterType(): void {
		$result = $this->executeCodeSnippet("type{^String => Real}->withParameterType(type{Boolean});");
		$this->assertEquals("type{^Boolean => Real}", $result);
	}

	public function testWithParameterTypeMetaType(): void {
		$result = $this->executeCodeSnippet("getWithParameterType(type{^String => Real});",
			valueDeclarations: "getWithParameterType = ^Type<Function> => Type<^Integer => Any> :: #->withParameterType(type{Integer});");
		$this->assertEquals("type{^Integer => Real}", $result);
	}

	public function testWithParameterTypeInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type',
			"type{String}->withParameterType(type{Integer});");
	}

	public function testWithParameterTypeInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type',
			"type{^Boolean => Real}->withParameterType(42);");
	}

}