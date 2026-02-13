<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\String;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsBytesTest extends CodeExecutionTestHelper {

	public function testAsBytesOk(): void {
		$result = $this->executeCodeSnippet("'test'->asBytes;");
		$this->assertEquals('"test"', $result);
	}

	public function testAsBytesType(): void {
		$result = $this->executeCodeSnippet("asStr('hello');",
			valueDeclarations: "asStr = ^b: String => Bytes :: b->as(`Bytes);"
		);
		$this->assertEquals('"hello"', $result);
	}

	public function testAsBytesLengthRange(): void {
		$result = $this->executeCodeSnippet("asStr('hello');",
			valueDeclarations: "asStr = ^b: String<3..8> => Bytes<3..32> :: b->as(`Bytes);"
		);
		$this->assertEquals('"hello"', $result);
	}

}