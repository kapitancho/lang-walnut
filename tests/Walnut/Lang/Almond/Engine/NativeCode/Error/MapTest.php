<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Error;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class MapTest extends CodeExecutionTestHelper {

	public function testMap(): void {
		$result = $this->executeCodeSnippet(
			"doError(@'error');",
			valueDeclarations: "
				doError = ^e: Error<String> => Error<String> ::
					e->map(^item: Integer => String :: item->asString);
			"
		);
		$this->assertEquals("@'error'", $result);
	}

}
