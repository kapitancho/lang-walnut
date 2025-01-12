<?php

namespace Walnut\Lang\NativeCode\String;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ConcatTest extends CodeExecutionTestHelper {

	public function testConcat(): void {
		$result = $this->executeCodeSnippet("'hello '->concat('world');");
		$this->assertEquals("'hello world'", $result);
	}

	public function testConcatSubtype(): void {
		$result = $this->executeCodeSnippet("'hello '->concat(getStr());", "S1 <: String<2..5>; S2 <: String<8..10>; getStr = ^Any => S1|S2 :: S1('world');");
		$this->assertEquals("'hello world'", $result);
	}

	public function testConcatInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "'hello'->concat(23);");
	}

}