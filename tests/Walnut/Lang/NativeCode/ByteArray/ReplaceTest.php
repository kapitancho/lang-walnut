<?php

namespace Walnut\Lang\NativeCode\ByteArray;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ReplaceTest extends CodeExecutionTestHelper {

	public function testReplaceString(): void {
		$result = $this->executeCodeSnippet('"** hello **"->replace[match: "*", replacement: "+"];');
		$this->assertEquals('"++ hello ++"', $result);
	}

	public function testReplaceRegexp(): void {
		$result = $this->executeCodeSnippet('"** hello **"->replace[match: RegExp(\'/\*+/\'), replacement: "+"];');
		$this->assertEquals('"+ hello +"', $result);
	}

	public function testReplaceInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type: Integer[42]",
			'"** hello **"->replace(42);');
	}

}
