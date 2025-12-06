<?php

namespace Walnut\Lang\Test\NativeCode\Real;

use PHPUnit\Framework\Attributes\DataProvider;
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

	public function testClampReturnRangesInfinity(): void {
		$result = $this->executeCodeSnippet(
			"clampFn[min: 2, max: 4];",
			valueDeclarations: "
				getReal = ^ => Real :: 11.2;
				clampFn = ^range: [min: Real<2..3>, max: Real<3..4>] => Real<2..4> :: getReal()->clamp(range);
			"
		);
		$this->assertEquals('4', $result);
	}

	#[DataProvider('clampReturnRangesProvider')]
	public function testClampReturnRanges(string $param, string $range, string $returnType, string $expected): void {
		$result = $this->executeCodeSnippet(
			"clampFn$param;",
			valueDeclarations: "
				getReal = ^ => Real<11.1..11.4> :: 11.2;
				clampFn = ^range: $range => $returnType :: getReal()->clamp(range);
			"
		);
		$this->assertEquals($expected, $result);
	}

	public static function clampReturnRangesProvider(): iterable {
		yield ['[min: 10.3]', '[min: Real<1.5..20>]', 'Real<11.1..20>', '11.2'];
		yield ['[min: 13.2]', '[min: Real<1.5..20>]', 'Real<11.1..20>', '13.2'];
		yield ['[:]', '[:]', 'Real<11.1..11.4>', '11.2'];
		yield ['[:]', '[min: ?Real<1.5..20>]', 'Real<11.1..20>', '11.2'];
		yield ['[min: 10.3]', '[min: ?Real<1.5..20>]', 'Real<11.1..20>', '11.2'];
		yield ['[min: 13.2]', '[min: ?Real<1.5..20>]', 'Real<11.1..20>', '13.2'];

		yield ['[min: 13.2]', '[min: Real<12.5..20>]', 'Real<12.5..20>', '13.2']; // <11.1..20>
		yield ['[:]', '[min: ?Real<12.5..20>]', 'Real<11.1..20>', '11.2'];
		yield ['[min: 13.2]', '[min: ?Real<12.5..20>]', 'Real<11.1..20>', '13.2'];

		yield ['[min: 10.3, max: 43.2]', '[min: Real<1.5..20>, max: Real<33.3..99.9>]', 'Real<11.1..20>', '11.2']; // <1.5..99.9>
		yield ['[min: 10.3]', '[min: Real<1.5..20>, max: ?Real<33.3..99.9>]', 'Real<11.1..20>', '11.2'];
		yield ['[min: 10.3, max: 43.2]', '[min: Real<1.5..20>, max: ?Real<33.3..99.9>]', 'Real<11.1..20>', '11.2'];

		yield ['[min: 10.3, max: 10.2]', '[min: Real<1.5..20>, max: Real<9.99..99.9>]', 'Result<Real<9.99..20>, InvalidRealRange>', '@InvalidRealRange![min: 10.3, max: 10.2]']; // <1.5..99.9>
		yield ['[min: 10.3, max: 10.4]', '[min: Real<1.5..20>, max: Real<9.99..99.9>]', 'Result<Real<9.99..20>, InvalidRealRange>', '10.4']; // <1.5..99.9>
		yield ['[min: 10.3, max: 43.2]', '[min: Real<1.5..20>, max: Real<9.99..99.9>]', 'Result<Real<9.99..20>, InvalidRealRange>', '11.2']; // <1.5..99.9>
		yield ['[min: 13.2, max: 43.2]', '[min: Real<1.5..20>, max: Real<9.99..99.9>]', 'Result<Real<9.99..20>, InvalidRealRange>', '13.2']; // <1.5..99.9>
		yield ['[min: 10.3]', '[min: Real<1.5..20>, max: ?Real<9.99..99.9>]', 'Result<Real<9.99..20>, InvalidRealRange>', '11.2'];
		yield ['[min: 10.3, max: 10.2]', '[min: Real<1.5..20>, max: ?Real<9.99..99.9>]', 'Result<Real<9.99..20>, InvalidRealRange>', '@InvalidRealRange![min: 10.3, max: 10.2]'];
		yield ['[min: 10.3, max: 10.4]', '[min: Real<1.5..20>, max: ?Real<9.99..99.9>]', 'Result<Real<9.99..20>, InvalidRealRange>', '10.4'];
		yield ['[min: 10.3, max: 43.2]', '[min: Real<1.5..20>, max: ?Real<9.99..99.9>]', 'Result<Real<9.99..20>, InvalidRealRange>', '11.2'];
		yield ['[min: 13.2, max: 43.2]', '[min: Real<1.5..20>, max: ?Real<9.99..99.9>]', 'Result<Real<9.99..20>, InvalidRealRange>', '13.2'];

		yield ['[min: 10.3, max: 10.2]', '[min: Real<1.5..20>, max: Real<9.99..10.9>]', 'Result<Real<9.99..10.9>, InvalidRealRange>', '@InvalidRealRange![min: 10.3, max: 10.2]']; // <1.5..11.4>>
		yield ['[min: 10.3, max: 10.4]', '[min: Real<1.5..20>, max: Real<9.99..10.9>]', 'Result<Real<9.99..10.9>, InvalidRealRange>', '10.4']; // <1.5..11.4>
		yield ['[min: 10.3]', '[min: Real<1.5..20>, max: ?Real<9.99..10.9>]', 'Result<Real<9.99..20>, InvalidRealRange>', '11.2'];
		yield ['[min: 10.3, max: 10.2]', '[min: Real<1.5..20>, max: ?Real<9.99..10.9>]', 'Result<Real<9.99..20>, InvalidRealRange>', '@InvalidRealRange![min: 10.3, max: 10.2]'];
		yield ['[min: 10.3, max: 10.4]', '[min: Real<1.5..20>, max: ?Real<9.99..10.9>]', 'Result<Real<9.99..20>, InvalidRealRange>', '10.4'];

		yield ['[min: 4.2, max: 20.4]', '[min: Real<1.5..6>, max: Real<19.99..20.9>]', 'Real<11.1..11.4>', '11.2'];

		yield ['[min: 18.3, max: 10.2]', '[min: Real<17.2..20>, max: Real<9.99..10.9>]', 'Error<InvalidRealRange>', '@InvalidRealRange![min: 18.3, max: 10.2]']; // <1.5..11.4>>
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
