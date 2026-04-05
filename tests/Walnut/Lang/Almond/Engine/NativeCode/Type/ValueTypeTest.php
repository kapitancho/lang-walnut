<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class ValueTypeTest extends CodeExecutionTestHelper {

	public function testValueTypeOpen(): void {
		$result = $this->executeCodeSnippet("type{MyOpen}->valueType;", "MyOpen := #[a: String];");
		$this->assertEquals("type[a: String]", $result);
	}

	public function testValueTypeOpenMetaType(): void {
		$result = $this->executeCodeSnippet("getValueType(`MyOpen);",
			"MyOpen := #[a: String];",
			"getValueType = ^t: Type<Open> => Type :: t->valueType;");
		$this->assertEquals("type[a: String]", $result);
	}

	public function testValueTypeOptional(): void {
		$result = $this->executeCodeSnippet("getValueType(type{Optional<Boolean>});",
			valueDeclarations: "getValueType = ^t: Type<Optional<Named>> => Type :: t->valueType;");
		$this->assertEquals("type{Boolean}", $result);
	}

	public function testValueTypeOptionalDirect(): void {
		$result = $this->executeCodeSnippet("getValueType(type{Boolean});",
			valueDeclarations: "getValueType = ^t: Type<Optional<Named>> => Type :: t->valueType;");
		$this->assertEquals("type{Boolean}", $result);
	}

	public function testValueTypeValue(): void {
		$result = $this->executeCodeSnippet("getValueType(type{Value<Boolean>});",
			valueDeclarations: "getValueType = ^t: Type<Value<Named>> => Type :: t->valueType;");
		$this->assertEquals("type{Boolean}", $result);
	}

	public function testValueTypeSealed(): void {
		$result = $this->executeCodeSnippet("type{MySealed}->valueType;", "MySealed := $[a: String];");
		$this->assertEquals("type[a: String]", $result);
	}

	public function testValueTypeSealedMetaType(): void {
		$result = $this->executeCodeSnippet("getValueType(`MySealed);",
			"MySealed := $[a: String];",
			"getValueType = ^Type<Sealed> => Type :: #->valueType;");
		$this->assertEquals("type[a: String]", $result);
	}

	public function testValueTypeMutable(): void {
		$result = $this->executeCodeSnippet("type{Mutable<String>}->valueType;");
		$this->assertEquals("type{String}", $result);
	}

	public function testValueTypeMutableMetaType(): void {
		$result = $this->executeCodeSnippet("getValueType(`Mutable<String>);",
			valueDeclarations: "getValueType = ^Type<MutableValue> => Type :: #->valueType;");
		$this->assertEquals("type{String}", $result);
	}

	public function testValueTypeInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('The type String does not have a value type',
			"type{String}->valueType;");
	}

}