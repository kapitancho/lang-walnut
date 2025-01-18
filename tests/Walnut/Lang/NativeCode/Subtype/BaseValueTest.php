<?php

namespace Walnut\Lang\NativeCode\Subtype;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BaseValueTest extends CodeExecutionTestHelper {

	public function testBaseValue(): void {
		$result = $this->executeCodeSnippet("MySubtype('Hello')->baseValue;", "MySubtype <: String;");
		$this->assertEquals("'Hello'", $result);
	}

	/* TODO - add support
	public function testBaseValueMetaType(): void {
		$result = $this->executeCodeSnippet("getBaseValue(MySubtype('Hello'));",
			"MySubtype <: String; getBaseValue = ^Subtype => String :: #->baseValue;");
		$this->assertEquals("'Hello'", $result);
	}
	*/
}