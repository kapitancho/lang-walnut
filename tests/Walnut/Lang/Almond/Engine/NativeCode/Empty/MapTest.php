<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Empty;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class MapTest extends CodeExecutionTestHelper {

	public function testMap(): void {
		$result = $this->executeCodeSnippet(
			"doEmpty(empty);",
			valueDeclarations: "
				doEmpty = ^e: Empty => Empty ::
					e->map(^item: Integer => String :: item->asString);
			"
		);
		$this->assertEquals("empty", $result);
	}

}
