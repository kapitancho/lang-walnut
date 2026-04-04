<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Error;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class FilterTest extends CodeExecutionTestHelper {

	public function testFilterOk(): void {
		$result = $this->executeCodeSnippet(
			"doError(@'error');",
			valueDeclarations: "
				doError = ^e: Error<String> => Error<String> ::
					e->filter(^item: Integer => Boolean :: item > 2);
			"
		);
		$this->assertEquals("@'error'", $result);
	}

	public function testFilterInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"The parameter of filter must be a function that returns a Boolean.",
			"doError('hello');",
			valueDeclarations: "
				doError = ^e: Error<String> => Error<String> ::
					e->filter(^item: Integer => Real :: item);
			"
		);
	}

}
