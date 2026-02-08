<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Mutable;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsRealTest extends CodeExecutionTestHelper {

	public function testAsRealPositive(): void {
		$result = $this->executeCodeSnippet("mutable{Real, 3.14}->asReal;");
		$this->assertEquals("3.14", $result);
	}

	public function testAsRealPositiveRounding(): void {
		$result = $this->executeCodeSnippet("mutable{Real, 3.77}->asReal;");
		$this->assertEquals("3.77", $result);
	}

	public function testAsRealNegative(): void {
		$result = $this->executeCodeSnippet("mutable{Real, -3.14}->asReal;");
		$this->assertEquals("-3.14", $result);
	}

	public function testAsRealNegativeRounding(): void {
		$result = $this->executeCodeSnippet("mutable{Real, -3.77}->asReal;");
		$this->assertEquals("-3.77", $result);
	}

	public function testAsRealOtherType(): void {
		$result = $this->executeCodeSnippet(
			"r(mutable{MyType, MyType(-3.77)});",
			"MyType := #Real; MyType ==> Real :: $$;",
			"r = ^v: Mutable<MyType> => Real :: v->asReal;"
		);
		$this->assertEquals("-3.77", $result);
	}

	public function testAsRealInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "mutable{Set, [;]}->asReal;");
	}

}