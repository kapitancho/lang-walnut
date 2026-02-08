<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\String;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class PadRightTest extends CodeExecutionTestHelper {

	public function testPadRightOk(): void {
		$result = $this->executeCodeSnippet("'hello'->padRight[length: 10, padString: '-'];");
		$this->assertEquals("'hello-----'", $result);
	}

	public function testPadRightNotNeeded(): void {
		$result = $this->executeCodeSnippet("'hello'->padRight[length: 4, padString: '-'];");
		$this->assertEquals("'hello'", $result);
	}

	public function testPadRightReturnType(): void {
		$result = $this->executeCodeSnippet("pad[str: 'hello', len: 10];",
			valueDeclarations: "pad = ^[str: String<4..8>, len: Integer<2..12>] => String<4..12> :: 
				#str->padRight[length: #len, padString: '-+'];"
		);
		$this->assertEquals("'hello-+-+-'", $result);
	}

	public function testPadRightInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "'hello'->padRight(5);");
	}

	public function testPadRightInvalidParameterKeys(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "'hello'->padRight[length: 10];");
	}

}