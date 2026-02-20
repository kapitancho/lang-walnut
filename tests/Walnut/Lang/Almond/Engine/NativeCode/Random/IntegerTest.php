<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Random;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class IntegerTest extends CodeExecutionTestHelper {

	public function testIntegerOk(): void {
		$result = $this->executeCodeSnippet("Random->integer[min: 5, max: 5];");
		$this->assertEquals("5", $result);
	}

	public function testIntegerOkRange(): void {
		$result = $this->executeCodeSnippet("Random->integer[min: -3, max: 3];");
		$this->assertLessThanOrEqual(3, $result);
		$this->assertGreaterThanOrEqual(-3, $result);
	}

	public function testIntegerInvalidParameterValue(): void {
		$this->executeErrorCodeSnippet('Expected a parameter type [min: Integer, max: Integer], but got [min: Integer[7]]', "Random->integer[min: 7];");
	}

	public function testIntegerInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "Random->integer;");
	}

}