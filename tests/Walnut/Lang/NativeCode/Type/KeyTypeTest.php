<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class KeyTypeTest extends CodeExecutionTestHelper {
	
	public function testKeyTypeMap(): void {
		$result = $this->executeCodeSnippet("type{Map<Integer>}->keyType;");
		$this->assertEquals("type{String}", $result);
	}

	public function testKeyTypeMapStringOfLength(): void {
		$result = $this->executeCodeSnippet("type{Map<String<2..5>:Integer>}->keyType;");
		$this->assertEquals("type{String<2..5>}", $result);
	}

	public function testKeyTypeMapSubset(): void {
		$result = $this->executeCodeSnippet("type{Map<String['a', 'b', 'c']:Integer>}->keyType;");
		$this->assertEquals("type{String['a', 'b', 'c']}", $result);
	}

	public function testKeyTypeInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type',
			"type{Array}->keyType;");
	}

}