<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\String;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class JsonDecodeTest extends CodeExecutionTestHelper {

	public function testJsonDecodeOk(): void {
		$result = $this->executeCodeSnippet("'[1, false]'->jsonDecode;");
		$this->assertEquals("[1, false]", $result);
	}

	public function testJsonDecodeInvalidJson(): void {
		$result = $this->executeCodeSnippet("'invalid json'->jsonDecode;");
		$this->assertEquals("@InvalidJsonString![value: 'invalid json']", $result);
	}

}