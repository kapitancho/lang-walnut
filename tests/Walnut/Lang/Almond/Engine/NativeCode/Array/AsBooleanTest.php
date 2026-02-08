<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsBooleanTest extends CodeExecutionTestHelper {

	public function testAsBooleanEmpty(): void {
		$result = $this->executeCodeSnippet("[]->asBoolean;");
		$this->assertEquals("false", $result);
	}

	public function testAsBooleanNotEmpty(): void {
		$result = $this->executeCodeSnippet("[1, 'hello']->asBoolean;");
		$this->assertEquals("true", $result);
	}

	public function testAsBooleanCast(): void {
		$result = $this->executeCodeSnippet("[1, 'hello']->as(`Boolean);");
		$this->assertEquals("true", $result);
	}

	public function testAsBooleanInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[1, 2]->asBoolean(5);");
	}

}
