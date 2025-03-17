<?php

namespace Walnut\Lang\Implementation\Function;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class CustomMethodAnalyserTest extends CodeExecutionTestHelper {

	public function testCustomMethodSignatureOk(): void {
		$result = $this->executeCodeSnippet("MyString('hello')->length", <<<NUT
			MyString = #String;
			MyString->length(=> Integer[999]) :: 999;
		NUT);
		$this->assertEquals('999', $result);
	}

}