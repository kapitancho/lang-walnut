<?php

namespace Walnut\Lang\Test\NativeCode\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class WithValueTypeTest extends CodeExecutionTestHelper {

	public function testWithValueTypeMutable(): void {
		$result = $this->executeCodeSnippet("type{Mutable<String>}->withValueType(type{Integer});");
		$this->assertEquals("type{Mutable<Integer>}", $result);
	}

	public function testWithValueTypeMutableMetaType(): void {
		$result = $this->executeCodeSnippet("getWithValueType(type{Mutable<String>});",
			valueDeclarations: "getWithValueType = ^Type<MutableValue> => Type<MutableValue> :: #->withValueType(type{Integer});");
		$this->assertEquals("type{Mutable<Integer>}", $result);
	}

	public function testWithValueTypeInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type',
			"type{String}->withValueType(type{Integer});");
	}

	public function testWithValueTypeInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type',
			"type{Mutable<String>}->withValueType(42);");
	}

}