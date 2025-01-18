<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ValueTypeTest extends CodeExecutionTestHelper {

	public function testValueTypeSealed(): void {
		$result = $this->executeCodeSnippet("type{MySealed}->valueType;", "MySealed = $[a: String];");
		$this->assertEquals("type[a: String]", $result);
	}

	public function testValueTypeSealedMetaType(): void {
		$result = $this->executeCodeSnippet("getValueType(type{MySealed});", "MySealed = $[a: String]; getValueType = ^Type<Sealed> => Type :: #->valueType;");
		$this->assertEquals("type[a: String]", $result);
	}

	public function testValueTypeMutable(): void {
		$result = $this->executeCodeSnippet("type{Mutable<String>}->valueType;");
		$this->assertEquals("type{String}", $result);
	}

	public function testValueTypeMutableMetaType(): void {
		$result = $this->executeCodeSnippet("getValueType(type{Mutable<String>});", "getValueType = ^Type<MutableType> => Type :: #->valueType;");
		$this->assertEquals("type{String}", $result);
	}

	public function testValueTypeInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type',
			"type{String}->valueType;");
	}

}