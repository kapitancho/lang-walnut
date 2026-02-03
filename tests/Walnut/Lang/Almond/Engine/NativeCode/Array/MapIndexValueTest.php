<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class MapIndexValueTest extends CodeExecutionTestHelper {

	public function testMapIndexValueEmpty(): void {
		$result = $this->executeCodeSnippet("[]->mapIndexValue(^[index: Integer, value: Integer] => Integer :: #index + #value);");
		$this->assertEquals("[]", $result);
	}

	public function testMapIndexValueNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1, 2, 5, 10, 5]->mapIndexValue(^[index: Integer, value: Integer] => Integer :: #index + #value);");
		$this->assertEquals("[1, 3, 7, 13, 9]", $result);
	}

	public function testMapIndexValueNonEmptyError(): void {
		$result = $this->executeCodeSnippet("[1, 2, 5, 10, 5]->mapIndexValue(^[index: Integer, value: Integer] => Result<Integer, String> :: @'error');");
		$this->assertEquals("@'error'", $result);
	}

	public function testMapIndexValueInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[1, 'a']->mapIndexValue(5);");
	}

	public function testMapIndexValueInvalidParameterParameterType(): void {
		$this->executeErrorCodeSnippet("of the callback function is not a subtype of",
			"[1, 2, 5, 10, 5]->mapIndexValue(^[index: Integer] => Integer :: #index + 3);");
	}

}