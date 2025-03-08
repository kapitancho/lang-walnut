<?php

namespace Walnut\Lang\NativeCode\String;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryPlusTest extends CodeExecutionTestHelper {

	public function testBinaryPlus(): void {
		$result = $this->executeCodeSnippet("'hello ' + 'world';");
		$this->assertEquals("'hello world'", $result);
	}

	public function testBinaryPlusSubtype(): void {
		$result = $this->executeCodeSnippet("'hello ' + getStr();", "S1 = <: String<2..5>; S2 = <: String<8..10>; getStr = ^Any => S1|S2 :: S1('world');");
		$this->assertEquals("'hello world'", $result);
	}

	public function testBinaryPlusInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "'hello' + 23;");
	}

}