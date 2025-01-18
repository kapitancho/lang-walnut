<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class WithRefTypeTest extends CodeExecutionTestHelper {

	public function testWithRefType(): void {
		$result = $this->executeCodeSnippet("type{Type<String>}->withRefType(type{Integer});");
		$this->assertEquals("type{Type<Integer>}", $result);
	}

	public function testWithRefTypeMetaType(): void {
		$result = $this->executeCodeSnippet("getWithRefType(type{Type<String>});",
			"getWithRefType = ^Type<Type> => Type :: #->withRefType(type{Integer});");
		$this->assertEquals("type{Type<Integer>}", $result);
	}

	public function testWithRefTypeInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type',
			"type{String}->withRefType(type{Integer});");
	}

	public function testWithRefTypeInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type',
			"type{Type<String>}->withRefType(42);");
	}

}