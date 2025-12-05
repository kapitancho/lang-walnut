<?php

namespace Walnut\Lang\Test\NativeCode\Real;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ClampTest extends CodeExecutionTestHelper {

	public function testClampNoConstraints(): void {
		$result = $this->executeCodeSnippet("5.5->clamp[:];");
		$this->assertEquals("5.5", $result);
	}

	public function testClampWithMin(): void {
		$result = $this->executeCodeSnippet("5.5->clamp[min: 10.3];");
		$this->assertEquals("10.3", $result);
	}

	public function testClampWithMax(): void {
		$result = $this->executeCodeSnippet("15.5->clamp[max: 10.3];");
		$this->assertEquals("10.3", $result);
	}

	public function testClampWithinRange(): void {
		$result = $this->executeCodeSnippet("5.5->clamp[min: 0.7, max: 10.3];");
		$this->assertEquals("5.5", $result);
	}

	public function testClampBelowMin(): void {
		$result = $this->executeCodeSnippet("-5.5->clamp[min: 0.7, max: 10.3];");
		$this->assertEquals("0.7", $result);
	}

	public function testClampAboveMax(): void {
		$result = $this->executeCodeSnippet("15.5->clamp[min: 0.7, max: 10.3];");
		$this->assertEquals("10.3", $result);
	}

	public function testClampExactMin(): void {
		$result = $this->executeCodeSnippet("0.7->clamp[min: 0.7, max: 10.3];");
		$this->assertEquals("0.7", $result);
	}

	public function testClampExactMax(): void {
		$result = $this->executeCodeSnippet("10.3->clamp[min: 0.7, max: 10.3];");
		$this->assertEquals("10.3", $result);
	}

	public function testClampNegativeValues(): void {
		$result = $this->executeCodeSnippet("-5.5->clamp[min: -10.0, max: -1.0];");
		$this->assertEquals("-5.5", $result);
	}

	public function testClampWithIntegerBounds(): void {
		$result = $this->executeCodeSnippet("5.5->clamp[min: 0, max: 10];");
		$this->assertEquals("5.5", $result);
	}

	public function testClampInvalidRange(): void {
		$result = $this->executeCodeSnippet("5.0->clamp[min: 10.7, max: 5.3];");
		$this->assertEquals("@InvalidRealRange![min: 10.7, max: 5.3]", $result);
	}

	public function testClampReturnTypeNoResult(): void {
		$result = $this->executeCodeSnippet(
			"clampFn[min: 5.2, max: 10.7];",
			valueDeclarations: "clampFn = ^range: [min: Real<1.3..6.4>, max: Real<7.9..11.9>] => Real<1.3..11.9> :: 11.2->clamp(range);"
		);
		$this->assertEquals("10.7", $result);
	}

	public function testClampReturnTypeResultReal(): void {
		$result = $this->executeCodeSnippet(
			"clampFn[min: 5.2, max: 10.7];",
			valueDeclarations: "clampFn = ^range: [min: Real<1.3..9.4>, max: Real<7.9..11.9>] => Result<Real<1.3..11.9>, InvalidRealRange> :: 11.2->clamp(range);"
		);
		$this->assertEquals("10.7", $result);
	}

	public function testClampReturnTypeResultInt(): void {
		$result = $this->executeCodeSnippet(
			"clampFn[min: 5, max: 11];",
			valueDeclarations: "clampFn = ^range: [min: Integer<1..9>, max: Integer<8..12>] => Result<Real<1..12>, InvalidIntegerRange> :: 11.2->clamp(range);"
		);
		$this->assertEquals("11", $result);
	}

	public function testClampReturnTypeResultIntError(): void {
		$result = $this->executeCodeSnippet(
			"clampFn[min: 9, max: 8];",
			valueDeclarations: "clampFn = ^range: [min: Integer<1..9>, max: Integer<8..12>] => Result<Real<1..12>, InvalidIntegerRange> :: 11.2->clamp(range);"
		);
		$this->assertEquals("@InvalidIntegerRange![min: 9, max: 8]", $result);
	}

	public function testClampInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "5.0->clamp(5);");
	}

}
