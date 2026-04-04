<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

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

	public function testWithValueTypeOptionalType(): void {
		$result = $this->executeCodeSnippet("getWithValueType(type{Optional<String>});",
			valueDeclarations: "getWithValueType = ^Type<Optional> => Type<Optional> :: #->withValueType(type{Integer});");
		$this->assertEquals("type{Optional<Integer>}", $result);
	}

	public function testWithValueTypeInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Target ref type must be a Mutable type or an Optional type, got: String',
			"type{String}->withValueType(type{Integer});");
	}

	public function testWithValueTypeInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type',
			"type{Mutable<String>}->withValueType(42);");
	}

}