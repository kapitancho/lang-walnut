<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class AsJsonValueTest extends CodeExecutionTestHelper {

	public function testAsJsonValueNonJson(): void {
		$result = $this->executeCodeSnippet("MyAtom[]->asJsonValue;", "MyAtom = :[];");
		$this->assertEquals("@InvalidJsonValue[value: MyAtom[]]", $result);
	}

	public function testAsJsonValueJson(): void {
		$result = $this->executeCodeSnippet("null->asJsonValue;");
		$this->assertEquals("null", $result);
	}

}