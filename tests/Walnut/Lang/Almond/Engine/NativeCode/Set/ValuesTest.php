<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Set;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class ValuesTest extends CodeExecutionTestHelper {

	public function testValuesEmpty(): void {
		$result = $this->executeCodeSnippet("[;]->values;");
		$this->assertEquals("[]", $result);
	}

	public function testValuesNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1; 2; 5.3; 2]->values;");
		$this->assertEquals("[1, 2, 5.3]", $result);
	}

	public function testValuesReturnType(): void {
		$result = $this->executeCodeSnippet("values[1; 2; 'hello'; 2];",
			valueDeclarations: "values = ^s: Set<Integer|String<5>> => Array<Integer|String<5>> :: s->values;"
		);
		$this->assertEquals("[1, 2, 'hello']", $result);
	}

}