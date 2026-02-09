<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Mutable;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsBooleanTest extends CodeExecutionTestHelper {

	public function testAsBoolean(): void {
		$result = $this->executeCodeSnippet("mutable{String, ''}->asBoolean;");
		$this->assertEquals("false", $result);
	}

	public function testAsBooleanReal(): void {
		$result = $this->executeCodeSnippet("mutable{Real, 3.14}->asBoolean;");
		$this->assertEquals("true", $result);
	}

	public function testAsBooleanOtherType(): void {
		$result = $this->executeCodeSnippet(
			"r(mutable{MyType, MyType(false)});",
			"MyType := #Boolean; MyType ==> Boolean :: $$;",
			"r = ^v: Mutable<MyType> => Boolean :: v->asBoolean;"
		);
		$this->assertEquals("false", $result);
	}

}