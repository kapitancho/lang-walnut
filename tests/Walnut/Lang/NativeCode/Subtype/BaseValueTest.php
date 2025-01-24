<?php

namespace Walnut\Lang\NativeCode\Subtype;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BaseValueTest extends CodeExecutionTestHelper {

	public function testBaseValue(): void {
		$result = $this->executeCodeSnippet("MySubtype('Hello')->baseValue;", "MySubtype <: String;");
		$this->assertEquals("'Hello'", $result);
	}

	public function testBaseValueMetaType(): void {
		$result = $this->executeCodeSnippet("getBaseValue(MySubtype('Hello'));",
			"MySubtype <: String; getBaseValue = ^Subtype => Any :: #->baseValue;");
		$this->assertEquals("'Hello'", $result);
	}
}