<?php

namespace Walnut\Lang\Test\NativeCode\Mutable;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ClearTest extends CodeExecutionTestHelper {

	public function testSetClear(): void {
		$result = $this->executeCodeSnippet("mutable{Set, [1; 2; 3]}->CLEAR;");
		$this->assertEquals("mutable{Set, [;]}", $result);
	}

	public function testSetClearInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "mutable{Set<2..>, [1; 2; 3]}->CLEAR;");
	}

	public function testClearInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "mutable{Real, 3.14}->CLEAR;");
	}

	public function testMapClear(): void {
		$result = $this->executeCodeSnippet("mutable{Map, [a: 1, b: 2, c: 3]}->CLEAR;");
		$this->assertEquals("mutable{Map, [:]}", $result);
	}

	public function testMapClearInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "mutable{Map<2..>, [a: 1, b: 2, c: 3]}->CLEAR;");
	}

	public function testRecordClear(): void {
		$result = $this->executeCodeSnippet("mutable{[a: ?String, b: ?Integer, ...Integer], [a: 'hello', b: 2, c: 3]}->CLEAR;");
		$this->assertEquals("mutable{[\n	a: OptionalKey<String>,\n	b: OptionalKey<Integer>,\n... Integer\n], [:]}", $result);
	}

	public function testRecordClearInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "mutable{[a: Integer, b: ?Integer, ...Integer], [a: 1, b: 2, c: 3]}->CLEAR");
	}

}