<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class WithKeyTypeTest extends CodeExecutionTestHelper {

	public function testWithKeyTypeMap(): void {
		$result = $this->executeCodeSnippet("type{Map<String<2>:Integer, 1..3>}->withKeyType(`String<1>);");
		$this->assertEquals("type{Map<String<1>:Integer, 1..3>}", $result);
	}

	public function testWithKeyTypeInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type',
			"type{String}->withKeyType(`String);");
	}

	public function testWithKeyTypeInvalidParameterTypeParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type',
			"type{String}->withKeyType(`Integer);");
	}

	public function testWithKeyTypeInvalidParameterTypeSubtype(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type',
			"type{Map<String>}->withKeyType(`Integer);");
	}

	public function testWithKeyTypeInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type',
			"type{Map<String>}->withKeyType(1)");
	}

}