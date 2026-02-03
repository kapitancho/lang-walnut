<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Integer;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class ClampTest extends CodeExecutionTestHelper {

	public function testClampNoConstraints(): void {
		$result = $this->executeCodeSnippet("5->clamp[:];");
		$this->assertEquals("5", $result);
	}

	public function testClampWithMin(): void {
		$result = $this->executeCodeSnippet("5->clamp[min: 10];");
		$this->assertEquals("10", $result);
	}

	public function testClampWithMax(): void {
		$result = $this->executeCodeSnippet("15->clamp[max: 10];");
		$this->assertEquals("10", $result);
	}

	public function testClampWithinRange(): void {
		$result = $this->executeCodeSnippet("5->clamp[min: 0, max: 10];");
		$this->assertEquals("5", $result);
	}

	public function testClampBelowMin(): void {
		$result = $this->executeCodeSnippet("-5->clamp[min: 0, max: 10];");
		$this->assertEquals("0", $result);
	}

	public function testClampAboveMax(): void {
		$result = $this->executeCodeSnippet("15->clamp[min: 0, max: 10];");
		$this->assertEquals("10", $result);
	}

	public function testClampExactMin(): void {
		$result = $this->executeCodeSnippet("0->clamp[min: 0, max: 10];");
		$this->assertEquals("0", $result);
	}

	public function testClampExactMax(): void {
		$result = $this->executeCodeSnippet("10->clamp[min: 0, max: 10];");
		$this->assertEquals("10", $result);
	}

	public function testClampNegativeValues(): void {
		$result = $this->executeCodeSnippet("-5->clamp[min: -10, max: -1];");
		$this->assertEquals("-5", $result);
	}

	public function testClampReturnsInteger(): void {
		$result = $this->executeCodeSnippet(
			"checkType(5->clamp[min: 0, max: 10])",
			valueDeclarations: "checkType = ^v: Integer => String :: 'Integer';"
		);
		$this->assertEquals("'Integer'", $result);
	}

	public function testClampWithRealMinReturnsReal(): void {
		$result = $this->executeCodeSnippet(
			"checkType(5->clamp[min: 0.0, max: 10])",
			valueDeclarations: "checkType = ^v: Real => String :: 'Real';"
		);
		$this->assertEquals("'Real'", $result);
	}

	public function testClampWithRealMaxReturnsReal(): void {
		$result = $this->executeCodeSnippet(
			"checkType(5->clamp[min: 0, max: 10.0])",
			valueDeclarations: "checkType = ^v: Real => String :: 'Real';"
		);
		$this->assertEquals("'Real'", $result);
	}

	public function testClampInvalidRange(): void {
		$result = $this->executeCodeSnippet("5->clamp[min: 10, max: 5];");
		$this->assertEquals("@InvalidIntegerRange![min: 10, max: 5]", $result);
	}

	public function testClampInvalidRangeWithReal(): void {
		$result = $this->executeCodeSnippet("5->clamp[min: 10.2, max: 5.0];");
		$this->assertEquals("@InvalidRealRange![min: 10.2, max: 5]", $result);
	}

	public function testClampReturnTypeNoResult(): void {
		$result = $this->executeCodeSnippet(
			"clampFn[min: 5, max: 10];",
			valueDeclarations: "clampFn = ^range: [min: Integer<1..6>, max: Integer<8..12>] => Integer<1..12> :: 11->clamp(range);"
		);
		$this->assertEquals("10", $result);
	}

	public function testClampReturnTypeResultInt(): void {
		$result = $this->executeCodeSnippet(
			"clampFn[min: 5, max: 10];",
			valueDeclarations: "clampFn = ^range: [min: Integer<1..9>, max: Integer<8..12>] => Result<Integer<1..12>, InvalidIntegerRange> :: 11->clamp(range);"
		);
		$this->assertEquals("10", $result);
	}

	public function testClampReturnTypeResultIntError(): void {
		$result = $this->executeCodeSnippet(
			"clampFn[min: 9, max: 8];",
			valueDeclarations: "clampFn = ^range: [min: Integer<1..9>, max: Integer<8..12>] => Result<Integer<1..12>, InvalidIntegerRange> :: 11->clamp(range);"
		);
		$this->assertEquals("@InvalidIntegerRange![min: 9, max: 8]", $result);
	}

	public function testClampReturnTypeResultReal(): void {
		$result = $this->executeCodeSnippet(
			"clampFn[min: 5, max: 10.1];",
			valueDeclarations: "clampFn = ^range: [min: Integer<1..9>, max: Real<8..12>] => Result<Real<1..12>, InvalidRealRange> :: 11->clamp(range);"
		);
		$this->assertEquals("10.1", $result);
	}

	public function testClampReturnTypeResultRealError(): void {
		$result = $this->executeCodeSnippet(
			"clampFn[min: 9, max: 8.1];",
			valueDeclarations: "clampFn = ^range: [min: Integer<1..9>, max: Real<8..12>] => Result<Real<1..12>, InvalidRealRange> :: 11->clamp(range);"
		);
		$this->assertEquals("@InvalidRealRange![min: 9, max: 8.1]", $result);
	}

	public function testClampInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "5->clamp(5);");
	}

}
