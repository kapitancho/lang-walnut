<?php

namespace Walnut\Lang\Test\NativeCode\String;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class LastPositionOfTest extends CodeExecutionTestHelper {

	public function testLastPositionOfYes(): void {
		$result = $this->executeCodeSnippet("'hello lower'->lastPositionOf('lo');");
		$this->assertEquals('6', $result);
	}

	public function testLastPositionOfNo(): void {
		$result = $this->executeCodeSnippet("'hello'->lastPositionOf('elo');");
		$this->assertEquals('@SubstringNotInString', $result);
	}

	public function testLastPositionOfInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "'hello'->lastPositionOf(23);");
	}

}