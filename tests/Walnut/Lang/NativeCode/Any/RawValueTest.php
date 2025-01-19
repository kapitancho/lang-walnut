<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class RawValueTest extends CodeExecutionTestHelper {

	public function testRawValueRaw(): void {
		$result = $this->executeCodeSnippet("5->rawValue;");
		$this->assertEquals("5", $result);
	}

	public function testRawValueSubtype(): void {
		$result = $this->executeCodeSnippet("MySubSub(MySub(5))->rawValue;", "MySub <: Integer; MySubSub <: MySub;");
		$this->assertEquals("5", $result);
	}

}