<?php

namespace Walnut\Lang\NativeCode\Integer;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class UpToTest extends CodeExecutionTestHelper {

	public function testUpTo(): void {
		$result = $this->executeCodeSnippet("3->upTo(5);");
		$this->assertEquals("[3, 4, 5]", $result);
	}

	public function testUpToEmpty(): void {
		$result = $this->executeCodeSnippet("3->upTo(1);");
		$this->assertEquals("[]", $result);
	}

	public function testBinaryPlusInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "3->upTo('hello');");
	}

}