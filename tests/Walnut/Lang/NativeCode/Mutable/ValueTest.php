<?php

namespace Walnut\Lang\Test\NativeCode\Mutable;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ValueTest extends CodeExecutionTestHelper {

	public function testValue(): void {
		$result = $this->executeCodeSnippet("mutable{Real, 3.14}->value;");
		$this->assertEquals("3.14", $result);
	}

	public function testValueMetaType(): void {
		$result = $this->executeCodeSnippet("getValue(mutable{Real, 3.14});", valueDeclarations: "getValue = ^MutableValue => Any :: #->value;");
		$this->assertEquals("3.14", $result);
	}

	public function testDataValueOk(): void {
		$result = $this->executeCodeSnippet("val{T!1}->value;", "T := Integer;");
		$this->assertEquals("1", $result);
	}

	public function testDataValueError(): void {
		$this->executeErrorCodeSnippet(
			"The value of the data type T should be a subtype of String but got Integer[1]",
			"val{T!1}->value;", "T := String;"
		);
	}

	public function testMutableValueOk(): void {
		$result = $this->executeCodeSnippet("val{mutable{Integer, 1}}->value;");
		$this->assertEquals("1", $result);
	}

	public function testMutableValueError(): void {
		$this->executeErrorCodeSnippet(
			"The value of the mutable type should be a subtype of String but got Integer[1]",
			"val{mutable{String, 1}}->value"
		);
	}

}