<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class ValueTypeTest extends CodeExecutionTestHelper {

	public function testValueTypeOpen(): void {
		$result = $this->executeCodeSnippet("type{MyOpen}->valueType;", "MyOpen := #[a: String];");
		$this->assertEquals("type[a: String]", $result);
	}

	public function testValueTypeOpenMetaType(): void {
		$result = $this->executeCodeSnippet("getValueType(type{MyOpen});",
			"MyOpen := #[a: String];",
			"getValueType = ^Type<Open> => Type :: #->valueType;");
		$this->assertEquals("type[a: String]", $result);
	}

	public function testValueTypeOptionalKey(): void {
		$result = $this->executeCodeSnippet("getValueType(type{Named});",
			valueDeclarations: "getValueType = ^Type<OptionalKey<Named>> => Type :: #->valueType;");
		$this->assertEquals("type{Named}", $result);
	}

	public function testValueTypeSealed(): void {
		$result = $this->executeCodeSnippet("type{MySealed}->valueType;", "MySealed := $[a: String];");
		$this->assertEquals("type[a: String]", $result);
	}

	public function testValueTypeSealedMetaType(): void {
		$result = $this->executeCodeSnippet("getValueType(type{MySealed});",
			"MySealed := $[a: String];",
			"getValueType = ^Type<Sealed> => Type :: #->valueType;");
		$this->assertEquals("type[a: String]", $result);
	}

	public function testValueTypeMutable(): void {
		$result = $this->executeCodeSnippet("type{Mutable<String>}->valueType;");
		$this->assertEquals("type{String}", $result);
	}

	public function testValueTypeMutableMetaType(): void {
		$result = $this->executeCodeSnippet("getValueType(type{Mutable<String>});",
			valueDeclarations: "getValueType = ^Type<MutableValue> => Type :: #->valueType;");
		$this->assertEquals("type{String}", $result);
	}

	public function testValueTypeInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type',
			"type{String}->valueType;");
	}

}