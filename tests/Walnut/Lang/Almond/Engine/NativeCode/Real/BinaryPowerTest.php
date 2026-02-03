<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Real;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class BinaryPowerTest extends CodeExecutionTestHelper {

	public function testBinaryPower(): void {
		$result = $this->executeCodeSnippet("1.6 ** 2;");
		$this->assertEquals("2.56", $result);
	}

	public function testBinaryPower0(): void {
		$result = $this->executeCodeSnippet("1.6 ** 0;");
		$this->assertEquals("1", $result);
	}

	public function testBinaryPower1(): void {
		$result = $this->executeCodeSnippet("1.6 ** 1;");
		$this->assertEquals("1.6", $result);
	}

	public function testBinary0Power(): void {
		$result = $this->executeCodeSnippet("0 ** 3.12;");
		$this->assertEquals("0", $result);
	}

	public function testBinaryZeroType(): void {
		$result = $this->executeCodeSnippet(
			"myPower(0);",
			valueDeclarations: "myPower = ^base: Real<-5..5> => Real :: base ** 3.12;",
		);
		$this->assertEquals("0", $result);
	}

	public function testBinaryNonZeroType(): void {
		$result = $this->executeCodeSnippet(
			"myPower(1);",
			valueDeclarations: "myPower = ^base: NonZeroReal => NonZeroReal :: base ** 3.12;",
		);
		$this->assertEquals("1", $result);
	}

	public function testBinaryPowerReal(): void {
		$result = $this->executeCodeSnippet("1.2 ** 1.14;");
		$this->assertEquals("1.2310242848447", $result);
	}

	public function testBinaryPowerInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "3.14 ** 'hello';");
	}

}