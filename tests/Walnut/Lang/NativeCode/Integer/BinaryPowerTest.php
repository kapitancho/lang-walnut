<?php

namespace Walnut\Lang\Test\NativeCode\Integer;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryPowerTest extends CodeExecutionTestHelper {

	public function testBinaryPower(): void {
		$result = $this->executeCodeSnippet("3 ** 5;");
		$this->assertEquals("243", $result);
	}

	public function testBinaryPower0(): void {
		$result = $this->executeCodeSnippet("16 ** 0;");
		$this->assertEquals("1", $result);
	}

	public function testBinaryPower1(): void {
		$result = $this->executeCodeSnippet("16 ** 1;");
		$this->assertEquals("16", $result);
	}

	public function testBinary0Power(): void {
		$result = $this->executeCodeSnippet("0 ** 31;");
		$this->assertEquals("0", $result);
	}

	public function testBinaryZeroType(): void {
		$result = $this->executeCodeSnippet(
			"myPower(0);",
			valueDeclarations: "myPower = ^base: Integer<-5..5> => Integer :: base ** 31;",
		);
		$this->assertEquals("0", $result);
	}

	public function testBinaryNonZeroType(): void {
		$result = $this->executeCodeSnippet(
			"myPower(1);",
			valueDeclarations: "myPower = ^base: NonZeroInteger => NonZeroInteger :: base ** 31;",
		);
		$this->assertEquals("1", $result);
	}

	public function testBinaryPowerReal(): void {
		$result = $this->executeCodeSnippet("3 ** 1.14;");
		$this->assertEquals("3.4987928502728", $result);
	}

	public function testBinaryPowerInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "3 ** 'hello';");
	}

	public function testBinaryPowerByZeroReturnsOne(): void {
		$result = $this->executeCodeSnippet("42 ** 0;");
		$this->assertEquals("1", $result);
	}

	public function testBinaryPowerByZeroAlwaysReturnsOne(): void {
		$result = $this->executeCodeSnippet("7 ** 0;");
		$this->assertEquals("1", $result);
	}

	public function testBinaryPowerByOneReturnsInteger(): void {
		$result = $this->executeCodeSnippet("42 ** 1;");
		$this->assertEquals("42", $result);
	}

	public function testBinaryPowerByOnePreservesValue(): void {
		$result = $this->executeCodeSnippet("50 ** 1;");
		$this->assertEquals("50", $result);
	}

	public function testBinaryPowerByOneWithNegative(): void {
		$result = $this->executeCodeSnippet("negFive ** 1;", valueDeclarations: "negFive = -5;");
		$this->assertEquals("-5", $result);
	}

}