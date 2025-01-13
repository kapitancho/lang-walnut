<?php

namespace Walnut\Lang\NativeCode\Integer;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryPowerTest extends CodeExecutionTestHelper {

	public function testBinaryPower(): void {
		$result = $this->executeCodeSnippet("3 ** 5;");
		$this->assertEquals("243", $result);
	}

	public function testBinaryPowerReal(): void {
		$result = $this->executeCodeSnippet("3 ** 1.14;");
		$this->assertEquals("3.4987928502728", $result);
	}

	public function testBinaryPowerInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "3 ** 'hello';");
	}

}