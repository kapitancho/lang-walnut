<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Bytes;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class ReplaceTest extends CodeExecutionTestHelper {

	public function testReplaceString(): void {
		$result = $this->executeCodeSnippet('"** hello **"->replace[match: "*", replacement: "+"];');
		$this->assertEquals('"++ hello ++"', $result);
	}

	public function testReplaceRegexp(): void {
		$result = $this->executeCodeSnippet('"** hello **"->replace[match: \'/\*+/\'->asRegExp, replacement: "+"];');
		$this->assertEquals('"+ hello +"', $result);
	}

	public function testReplaceInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"The parameter type Integer[42] is not a valid replacement record [match: Bytes|RegExp, replacement: Bytes]",
			'"** hello **"->replace(42);');
	}

}
