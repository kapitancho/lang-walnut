<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\String;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class ConcatListTest extends CodeExecutionTestHelper {

	public function testConcatList(): void {
		$result = $this->executeCodeSnippet("'hello '->concatList['world', '!'];");
		$this->assertEquals("'hello world!'", $result);
	}

	public function testConcatListInvalidArrayParameter(): void {
		$this->executeErrorCodeSnippet("Expected an Array<String> as parameter type, got [Integer[15], String['World']]", "'hello'->concatList[15, 'World'];");
	}

	public function testConcatListInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "'hello'->concatList(23);");
	}

}