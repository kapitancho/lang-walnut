<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\String;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class PositionOfTest extends CodeExecutionTestHelper {

	public function testPositionOfYes(): void {
		$result = $this->executeCodeSnippet("'hello lower'->positionOf('lo');");
		$this->assertEquals('3', $result);
	}

	public function testPositionOfNo(): void {
		$result = $this->executeCodeSnippet("'hello'->positionOf('elo');");
		$this->assertEquals('@SubstringNotInString', $result);
	}

	public function testPositionOfInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "'hello'->positionOf(23);");
	}

}