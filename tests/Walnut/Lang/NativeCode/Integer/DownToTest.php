<?php

namespace Walnut\Lang\NativeCode\Integer;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class DownToTest extends CodeExecutionTestHelper {

	public function testDownToEmpty(): void {
		$result = $this->executeCodeSnippet("3->downTo(5);");
		$this->assertEquals("[]", $result);
	}

	public function testDownTo(): void {
		$result = $this->executeCodeSnippet("3->downTo(1);");
		$this->assertEquals("[3, 2, 1]", $result);
	}

	public function testBinaryPlusInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "3->downTo('hello');");
	}

}