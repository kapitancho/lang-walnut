<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Empty;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class FilterTest extends CodeExecutionTestHelper {

	public function testFilterOk(): void {
		$result = $this->executeCodeSnippet(
			"doEmpty(empty);",
			valueDeclarations: "
				doEmpty = ^e: Empty => Empty ::
					e->filter(^item: Integer => Boolean :: item > 2);
			"
		);
		$this->assertEquals("empty", $result);
	}

	public function testFilterInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"The parameter of filter must be a function that returns a Boolean.",
			"doEmpty('hello');",
			valueDeclarations: "
				doEmpty = ^e: Empty => Empty ::
					e->filter(^item: Integer => Real :: item);
			"
		);
	}

}
