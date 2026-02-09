<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Mutable;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsStringTest extends CodeExecutionTestHelper {

	public function testAsString(): void {
		$result = $this->executeCodeSnippet("mutable{String, 'hello'}->asString;");
		$this->assertEquals("'hello'", $result);
	}

	public function testAsStringReal(): void {
		$result = $this->executeCodeSnippet("mutable{Real, 3.14}->asString;");
		$this->assertEquals("'3.14'", $result);
	}

	public function testAsStringOtherType(): void {
		$result = $this->executeCodeSnippet(
			"r(mutable{MyType, MyType('hello')});",
			"MyType := #String; MyType ==> String :: $$;",
			"r = ^v: Mutable<MyType> => String :: v->asString;"
		);
		$this->assertEquals("'hello'", $result);
	}

}