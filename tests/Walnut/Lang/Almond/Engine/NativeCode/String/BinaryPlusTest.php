<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\String;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class BinaryPlusTest extends CodeExecutionTestHelper {

	public function testBinaryPlus(): void {
		$result = $this->executeCodeSnippet("'hello ' + 'world';");
		$this->assertEquals("'hello world'", $result);
	}

	public function testBinaryPlusShape(): void {
		$result = $this->executeCodeSnippet(
			"'hello '->myConcat('world');",
			typeDeclarations: "String->myConcat(^str: {String} => String) :: $ + str;"
		);
		$this->assertEquals("'hello world'", $result);
	}

	public function testBinaryPlusShapeConverted(): void {
		$result = $this->executeCodeSnippet(
			"'hello '->myConcat(A);",
			typeDeclarations: "
				A := ();
				A ==> String :: 'world';
				String->myConcat(^str: {String} => String) :: $ + str;
			"
		);
		$this->assertEquals("'hello world'", $result);
	}

	public function testBinaryPlusInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "'hello' + A;",
			"A := (); B := (); A ==> String @ B :: @B;");
	}

}