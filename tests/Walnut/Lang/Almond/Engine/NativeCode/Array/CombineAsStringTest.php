<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class CombineAsStringTest extends CodeExecutionTestHelper {

	public function testCombineAsStringEmpty(): void {
		$result = $this->executeCodeSnippet("[]->combineAsString('---');");
		$this->assertEquals("''", $result);
	}

	public function testCombineAsStringNonEmptyNoSeparator(): void {
		$result = $this->executeCodeSnippet("['1', '2', '5', '10', '5']->combineAsString('');");
		$this->assertEquals("'125105'", $result);
	}

	public function testCombineAsStringNonEmptyWithSeparator(): void {
		$result = $this->executeCodeSnippet("['1', '2', '5', '10', '5']->combineAsString(' ');");
		$this->assertEquals("'1 2 5 10 5'", $result);
	}

	public function testCombineAsStringInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "[1, 2]->combineAsString(' ');");
	}

	public function testCombineAsStringInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "['1', '2']->combineAsString(0);");
	}

}