<?php

namespace Walnut\Lang\NativeCode\Function;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryBitwiseOrTest extends CodeExecutionTestHelper {

	// Error cases
	public function testComposeIncompatibleTypes(): void {
		$this->executeErrorCodeSnippet(
			"Cannot compose functions: parameter type String of first function is not a subtype of parameter type Integer of second function",
			"add1 | toStr;",
			valueDeclarations: "add1 = ^x: String => Result<String> :: x + '.'; toStr = ^x: Integer => String :: x->asString;"
		);
	}

	public function testComposeNonFunctionSecond(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"composed = add1 | 5;",
			valueDeclarations: "add1 = ^x: Integer => Integer :: x + 1;"
		);
	}



	// Result special cases
	public function testNoResultFn1(): void {
		$result = $this->executeCodeSnippet(
			"fnC('3');",
			valueDeclarations: "
				fn1 = ^s: String => Result<Integer, NotANumber> :: s->asInteger;
				fn2 = ^s: String => Real :: 3.14 + s->length;
				fnC = ^s: String => Real :: {fn1 | fn2}(s);
			"
		);
		$this->assertEquals("3", $result);
	}

	public function testNoResultFn1Error(): void {
		$result = $this->executeCodeSnippet(
			"fnC('three');",
			valueDeclarations: "
				fn1 = ^s: String => Result<Integer, NotANumber> :: s->asInteger;
				fn2 = ^s: String => Real :: 3.14 + s->length;
				fnC = ^s: String => Real :: {fn1 | fn2}(s);
			"
		);
		$this->assertEquals("8.14", $result);
	}

	public function testResultSkipFn2(): void {
		$result = $this->executeCodeSnippet(
			"fnC(7);",
			valueDeclarations: "
				fn1 = ^i: Integer<0..> => Integer<-3..> :: i - 3;
				fn2 = ^i: Integer<0..> => Result<Real<0..>, NotANumber> :: i->sqrt;
				fnC = ^i: Integer<0..> => Integer<-3..> :: {fn1 | fn2}(i);
			"
		);
		$this->assertEquals("4", $result);
	}

	public function testResultFn2Error(): void {
		$result = $this->executeCodeSnippet(
			"fnC('hi');",
			valueDeclarations: "
				fn1 = ^s: String => Integer<-3..> :: s->length - 3;
				fn2 = ^i: Integer<-3..> => Result<Real<0..>, NotANumber> :: i->sqrt;
				fnC = ^s: String => Result<Real<0..>, NotANumber> :: {fn1 & fn2}(s);
			"
		);
		$this->assertEquals("@NotANumber", $result);
	}

	public function testResultBothCase1(): void {
		$result = $this->executeCodeSnippet(
			"fnC(25);",
			valueDeclarations: "
				fn1 = ^i: Integer => Result<Real, NotANumber> :: i->sqrt;
				fn2 = ^i: Integer => Result<String<3..>, HydrationError> :: i->hydrateAs(`String<3..>);
				fnC = ^i: Integer => Result<Real|String<2..>, HydrationError> :: {fn1 | fn2}(i);
			"
		);
		$this->assertEquals("5", $result);
	}

	public function testResultBothCase2(): void {
		$result = $this->executeCodeSnippet(
			"fnC(-25);",
			valueDeclarations: "
				fn1 = ^i: Integer => Result<Real, NotANumber> :: i->sqrt;
				fn2 = ^i: Integer => Result<String<3..>, HydrationError> :: i->asString->hydrateAs(`String<3..>);
				fnC = ^i: Integer => Result<Real|String<2..>, HydrationError> :: {fn1 | fn2}(i);
			"
		);
		$this->assertEquals("'-25'", $result);
	}

	public function testResultBothCase3(): void {
		$result = $this->executeCodeSnippet(
			"fnC(-5);",
			valueDeclarations: "
				fn1 = ^i: Integer => Result<Real, NotANumber> :: i->sqrt;
				fn2 = ^i: Integer => Result<String<3..>, HydrationError> :: i->asString->hydrateAs(`String<3..>);
				fnC = ^i: Integer => Result<Real|String<2..>, HydrationError> :: {fn1 | fn2}(i);
			"
		);
		$this->assertEquals("@HydrationError![\n	value: '-5',\n	hydrationPath: 'value',\n	errorMessage: 'The string value should be with a length between 3 and +Infinity'\n]", $result);
	}

}
