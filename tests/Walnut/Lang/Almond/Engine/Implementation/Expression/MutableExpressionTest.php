<?php

namespace Walnut\Lang\Test\Almond\Engine\Implementation\Expression;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class MutableExpressionTest extends CodeExecutionTestHelper {

	public function testMutable(): void {
		$result = $this->executeCodeSnippet("mutable{Integer, 42};");
		$this->assertEquals("mutable{Integer, 42}", $result);
	}

	public function testMutableNotASubtype(): void {
		$this->executeErrorCodeSnippet('is not a subtype', "mutable{Integer, 3.14};");
	}

	public function testMutableValueNotASubtype(): void {
		$this->executeErrorCodeSnippet(
			'is not a subtype of Integer',
			'v',
			valueDeclarations:  "v = mutable{Integer, 3.14};");
	}

}