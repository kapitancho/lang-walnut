<?php

namespace Walnut\Lang\NativeCode\Mutable;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ReverseTest extends CodeExecutionTestHelper {

	public function testReverseArrayEmpty(): void {
		$result = $this->executeCodeSnippet("mutable{Array<Integer>, []}->REVERSE;");
		$this->assertEquals("mutable{Array<Integer>, []}", $result);
	}

	public function testReverseArrayNonEmpty(): void {
		$result = $this->executeCodeSnippet("mutable{Array<Integer>, [1, 2]}->REVERSE;");
		$this->assertEquals("mutable{Array<Integer>, [2, 1]}", $result);
	}

	public function testReverseStringEmpty(): void {
		$result = $this->executeCodeSnippet("mutable{String, ''}->REVERSE;");
		$this->assertEquals("mutable{String, ''}", $result);
	}

	public function testReverseStringNonEmpty(): void {
		$result = $this->executeCodeSnippet("mutable{String, 'abc'}->REVERSE;");
		$this->assertEquals("mutable{String, 'cba'}", $result);
	}

	public function testReverseInvalidTargetType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid target type",
			"mutable{Map, [a: 1, b: 2]}->REVERSE;"
		);
	}

	public function testReverseInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"mutable{Array<Integer>, []}->REVERSE(1);"
		);
	}
}