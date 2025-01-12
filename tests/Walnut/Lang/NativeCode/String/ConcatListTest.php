<?php

namespace Walnut\Lang\NativeCode\String;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ConcatListTest extends CodeExecutionTestHelper {

	public function testConcatList(): void {
		$result = $this->executeCodeSnippet("'hello '->concatList['world', '!'];");
		$this->assertEquals("'hello world!'", $result);
	}

	public function testConcatListInvalidArrayParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "'hello'->concatList[15, 'World'];");
	}

	public function testConcatListInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "'hello'->concatList(23);");
	}

}