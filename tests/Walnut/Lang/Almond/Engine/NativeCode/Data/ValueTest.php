<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Data;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class ValueTest extends CodeExecutionTestHelper {

	public function testValue(): void {
		$result = $this->executeCodeSnippet("{MyTuple![3, 5]}->value;", "MyTuple := [Integer, Real];");
		$this->assertEquals("[3, 5]", $result);
	}

	public function testValueMetaType(): void {
		$result = $this->executeCodeSnippet("getValue(MyTuple![3, 5]);",
			"MyTuple := [Integer, Real];",
			"getValue = ^v: Data => Any :: v->value;",
		);
		$this->assertEquals("[3, 5]", $result);
	}

}