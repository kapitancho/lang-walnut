<?php

namespace Walnut\Lang\Test\NativeCode\Real;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class SignTest extends CodeExecutionTestHelper {

	public function testSignPositive(): void {
		$result = $this->executeCodeSnippet("3.14->sign;");
		$this->assertEquals("1", $result);
	}

	public function testSignPositiveInteger(): void {
		$result = $this->executeCodeSnippet("3->sign;");
		$this->assertEquals("1", $result);
	}

	public function testSignNegative(): void {
		$result = $this->executeCodeSnippet("-4.14->sign;");
		$this->assertEquals("-1", $result);
	}

	public function testSignNegativeInteger(): void {
		$result = $this->executeCodeSnippet("-4->sign;");
		$this->assertEquals("-1", $result);
	}

	public function testSignZero(): void {
		$result = $this->executeCodeSnippet("0.0->sign;");
		$this->assertEquals("0", $result);
	}

	public function testSignZeroInteger(): void {
		$result = $this->executeCodeSnippet("0->sign;");
		$this->assertEquals("0", $result);
	}

	public function testSignPositiveSmall(): void {
		$result = $this->executeCodeSnippet("0.001->sign;");
		$this->assertEquals("1", $result);
	}

	public function testSignNegativeSmall(): void {
		$result = $this->executeCodeSnippet("-0.001->sign;");
		$this->assertEquals("-1", $result);
	}

	public function testSignPositiveLarge(): void {
		$result = $this->executeCodeSnippet("999999.99->sign;");
		$this->assertEquals("1", $result);
	}

	public function testSignNegativeLarge(): void {
		$result = $this->executeCodeSnippet("-999999.99->sign;");
		$this->assertEquals("-1", $result);
	}

	public function testSignPositiveRange(): void {
		$result = $this->executeCodeSnippet(
			"sign(5.5)",
			valueDeclarations: "sign = ^num: Real<0.1..> => Integer[1] :: num->sign;",
		);
		$this->assertEquals("1", $result);
	}

	public function testSignNonNegativeRange(): void {
		$result = $this->executeCodeSnippet(
			"sign(5.5)",
			valueDeclarations: "sign = ^num: Real<0..> => Integer[0, 1] :: num->sign;",
		);
		$this->assertEquals("1", $result);
	}

	public function testSignNegativeRange(): void {
		$result = $this->executeCodeSnippet(
			"sign(-5.5)",
			valueDeclarations: "sign = ^num: Real<..-0.1> => Integer[-1] :: num->sign;",
		);
		$this->assertEquals("-1", $result);
	}

	public function testSignNonPositiveRange(): void {
		$result = $this->executeCodeSnippet(
			"sign(-5.5)",
			valueDeclarations: "sign = ^num: Real<..0> => Integer[-1, 0] :: num->sign;",
		);
		$this->assertEquals("-1", $result);
	}

	public function testSignZeroExact(): void {
		$result = $this->executeCodeSnippet(
			"sign(0.0)",
			valueDeclarations: "sign = ^num: Real<0..0> => Integer[0] :: num->sign;",
		);
		$this->assertEquals("0", $result);
	}

	public function testSignMixedRange(): void {
		$result = $this->executeCodeSnippet(
			"sign(-2.5)",
			valueDeclarations: "sign = ^num: Real<-10..10> => Integer[-1, 0, 1] :: num->sign;",
		);
		$this->assertEquals("-1", $result);
	}

	public function testSignInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"3.14->sign(5)"
		);
	}

}
