<?php

namespace Walnut\Lang\NativeCode\Real;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryPowerTest extends CodeExecutionTestHelper {

	public function testBinaryPower(): void {
		$result = $this->executeCodeSnippet("1.6 ** 2;");
		$this->assertEquals("2.56", $result);
	}

	public function testBinaryPowerReal(): void {
		$result = $this->executeCodeSnippet("1.2 ** 1.14;");
		$this->assertEquals("1.2310242848447", $result);
	}

	public function testBinaryPowerInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "3.14 ** 'hello';");
	}

}