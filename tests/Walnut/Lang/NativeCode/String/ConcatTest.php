<?php

namespace Walnut\Lang\Test\NativeCode\String;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ConcatTest extends CodeExecutionTestHelper {

	public function testConcat(): void {
		$result = $this->executeCodeSnippet("'hello '->concat('world');");
		$this->assertEquals("'hello world'", $result);
	}

	public function testConcatUnion(): void {
		$result = $this->executeCodeSnippet(
			"'hello '->myConcat('world');",
			typeDeclarations: "String->myConcat(^str: String['a', 'b', 'c']|String<5..> => String) :: $->concat(str);"
		);
		$this->assertEquals("'hello world'", $result);
	}

	public function testConcatInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "'hello'->concat(23);");
	}

}