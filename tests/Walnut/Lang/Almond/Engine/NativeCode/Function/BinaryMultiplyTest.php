<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Function;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class BinaryMultiplyTest extends CodeExecutionTestHelper {

	// Basic composition tests
	public function testComposeScopedVars(): void {
		$result = $this->executeCodeSnippet(
			"composed = getFn1() * getFn2(); composed(5);",
			valueDeclarations: "
				getFn1 = ^ => (^Integer => Integer) :: { a = 3; ^x: Integer => Integer :: x + a; }; 
				getFn2 = ^ => (^Integer => Integer) :: { b = 7; ^x: Integer => Integer :: x * b; };"
		);
		$this->assertEquals("56", $result);
	}

	public function testComposeDependency(): void {
		$result = $this->executeCodeSnippet(
			"composed = add1 * double; composed(5);",
			"T := Integer; ==> T :: T!1;",
			"add1 = ^x: Integer => Integer %% ~T :: x + t->value; double = ^x: Integer => Integer :: x * 2;"
		);
		$this->assertEquals("12", $result);
	}

	public function testComposeIntegerToInteger(): void {
		$result = $this->executeCodeSnippet(
			"composed = add1 * double; composed(5);",
			valueDeclarations: "add1 = ^x: Integer => Integer :: x + 1; double = ^x: Integer => Integer :: x * 2;"
		);
		$this->assertEquals("12", $result); // (5 + 1) * 2 = 12
	}

	public function testComposeIntegerToString(): void {
		$result = $this->executeCodeSnippet(
			"composed = add1 * asString; composed(5);",
			valueDeclarations: "add1 = ^x: Integer => Integer :: x + 1; asString = ^x: Integer => String :: x->asString;"
		);
		$this->assertEquals("'6'", $result); // (5 + 1)->asString = '6'
	}

	public function testComposeStringToInteger(): void {
		$result = $this->executeCodeSnippet(
			"composed = trim * length; composed('  hello  ');",
			valueDeclarations: "trim = ^s: String => String :: s->trim; length = ^s: String => Integer :: s->length;"
		);
		$this->assertEquals("5", $result); // '  hello  '->trim->length = 5
	}

	// Multiple composition
	public function testComposeThreeFunctions(): void {
		$result = $this->executeCodeSnippet(
			"pipeline = add1 * double + add1; pipeline(5);",
			valueDeclarations: "add1 = ^x: Integer => Integer :: x + 1; double = ^x: Integer => Integer :: x * 2;"
		);
		$this->assertEquals("13", $result); // ((5 + 1) * 2) + 1 = 13
	}

	public function testComposeFourFunctions(): void {
		$result = $this->executeCodeSnippet(
			"pipeline = add1 * double * add1 * double; pipeline(5);",
			valueDeclarations: "add1 = ^x: Integer => Integer :: x + 1; double = ^x: Integer => Integer :: x * 2;"
		);
		$this->assertEquals("26", $result); // (((5 + 1) * 2) + 1) * 2 = 26
	}

	// Type conversion chain
	public function testComposeTypeConversionChain(): void {
		$result = $this->executeCodeSnippet(
			"pipeline = toStr * toLen * double; pipeline(42);",
			valueDeclarations: "toStr = ^x: Integer => String :: x->asString; toLen = ^s: String => Integer :: s->length; double = ^x: Integer => Integer :: x * 2;"
		);
		$this->assertEquals("4", $result); // 42->asString->length = 2, then * 2 = 4
	}

	// Boolean composition
	public function testComposeBooleanFunctions(): void {
		$result = $this->executeCodeSnippet(
			"pipeline = isPositive * not; pipeline(5);",
			valueDeclarations: "isPositive = ^x: Integer => Boolean :: x > 0; not = ^b: Boolean => Boolean :: !b;"
		);
		$this->assertEquals("false", $result); // 5 > 0 = true, !true = false
	}

	public function testComposeBooleanChain(): void {
		$result = $this->executeCodeSnippet(
			"pipeline = isPositive * not * not; pipeline(5);",
			valueDeclarations: "isPositive = ^x: Integer => Boolean :: x > 0; not = ^b: Boolean => Boolean :: !b;"
		);
		$this->assertEquals("true", $result); // 5 > 0 = true, !true = false, !false = true
	}

	// Subtype composition
	public function testComposeWithSubtypes(): void {
		$result = $this->executeCodeSnippet(
			"pipeline = getInt * double; pipeline(null);",
			valueDeclarations: "getInt = ^x: Null => Integer :: 42; double = ^x: Integer => Integer :: x * 2;"
		);
		$this->assertEquals("84", $result); // null => 42 => 84
	}

	public function testComposeWithIntegerSubtypes(): void {
		$result = $this->executeCodeSnippet(
			"pipeline = makeSmall * double; pipeline(null);",
			valueDeclarations: "makeSmall = ^x: Null => Integer<1..10> :: 5; double = ^x: Integer => Integer :: x * 2;"
		);
		$this->assertEquals("10", $result); // null => 5 => 10
	}

	// Identity composition
	public function testComposeWithIdentity(): void {
		$result = $this->executeCodeSnippet(
			"pipeline = identity * add1; pipeline(5);",
			valueDeclarations: "identity = ^x: Integer => Integer :: x; add1 = ^x: Integer => Integer :: x + 1;"
		);
		$this->assertEquals("6", $result); // 5 => 5 => 6
	}

	// Array operations
	public function testComposeArrayOperations(): void {
		$result = $this->executeCodeSnippet(
			"pipeline = filterPositive * getLength; pipeline([1, -2, 3, -4, 5]);",
			valueDeclarations: "filterPositive = ^a: Array<Integer> => Array<Integer> :: a->filter(^i: Integer => Boolean :: i > 0); getLength = ^a: Array => Integer :: a->length;"
		);
		$this->assertEquals("3", $result); // [1, -2, 3, -4, 5]->filter(>0) = [1, 3, 5], length = 3
	}

	// Map operations
	public function testComposeMapOperations(): void {
		$result = $this->executeCodeSnippet(
			"pipeline = getValues * getLength; pipeline([a: 1, b: 2, c: 3]);",
			valueDeclarations: "getValues = ^m: Map => Array :: m->values; getLength = ^a: Array => Integer :: a->length;"
		);
		$this->assertEquals("3", $result);
	}

	// Error cases
	public function testComposeIncompatibleTypes(): void {
		$this->executeErrorCodeSnippet(
			"Cannot compose functions: return type String of first function is not a subtype of parameter type Integer of second function",
			"add1 * toStr;",
			valueDeclarations: "add1 = ^x: Integer => String :: x->asString; toStr = ^x: Integer => String :: x->asString;"
		);
	}

	public function testComposeNonFunctionSecond(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"composed = add1 * 5;",
			valueDeclarations: "add1 = ^x: Integer => Integer :: x + 1;"
		);
	}

	// Practical examples
	public function testPipelineDataProcessing(): void {
		$result = $this->executeCodeSnippet(
			"process = trim * lower * nonEmpty; process('  HELLO  ');",
			valueDeclarations: "trim = ^s: String => String :: s->trim; lower = ^s: String => String :: s->toLowerCase; nonEmpty = ^s: String => Boolean :: s->length > 0;"
		);
		$this->assertEquals("true", $result);
	}

	public function testPipelineDataProcessingEmpty(): void {
		$result = $this->executeCodeSnippet(
			"process = trim * lower * nonEmpty; process('     ');",
			valueDeclarations: "trim = ^s: String => String :: s->trim; lower = ^s: String => String :: s->toLowerCase; nonEmpty = ^s: String => Boolean :: s->length > 0;"
		);
		$this->assertEquals("false", $result);
	}

	public function testPipelineCalculation(): void {
		$result = $this->executeCodeSnippet(
			"calculate = add10 * double * sub5; calculate(3);",
			valueDeclarations: "add10 = ^x: Integer => Integer :: x + 10; double = ^x: Integer => Integer :: x * 2; sub5 = ^x: Integer => Integer :: x - 5;"
		);
		$this->assertEquals("21", $result); // (3 + 10) * 2 - 5 = 21
	}

	// Additional scoped variable tests
	public function testComposeScopedVarsChain(): void {
		$result = $this->executeCodeSnippet(
			"composed = getFn1() * getFn2() * getFn3(); composed(2);",
			valueDeclarations: "
				getFn1 = ^ => (^Integer => Integer) :: { a = 5; ^x: Integer => Integer :: x + a; };
				getFn2 = ^ => (^Integer => Integer) :: { b = 3; ^x: Integer => Integer :: x * b; };
				getFn3 = ^ => (^Integer => Integer) :: { c = 1; ^x: Integer => Integer :: x - c; };"
		);
		$this->assertEquals("20", $result); // ((2 + 5) * 3) - 1 = 20
	}

	public function testComposeScopedVarsMultipleVars(): void {
		$result = $this->executeCodeSnippet(
			"composed = getFn1() * getFn2(); composed(10);",
			valueDeclarations: "
				getFn1 = ^ => (^Integer => Integer) :: { a = 3; b = 2; ^x: Integer => Integer :: x + a + b; };
				getFn2 = ^ => (^Integer => Integer) :: { c = 5; d = 1; ^x: Integer => Integer :: x * c - d; };"
		);
		$this->assertEquals("74", $result); // (10 + 3 + 2) * 5 - 1 = 74
	}

	public function testComposeScopedVarsSameVarNames(): void {
		$result = $this->executeCodeSnippet(
			"composed = getFn1() * getFn2(); composed(4);",
			valueDeclarations: "
				getFn1 = ^ => (^Integer => Integer) :: { v = 6; ^x: Integer => Integer :: x + v; };
				getFn2 = ^ => (^Integer => Integer) :: { v = 2; ^x: Integer => Integer :: x * v; };"
		);
		$this->assertEquals("20", $result); // (4 + 6) * 2 = 20 (each function has its own 'v')
	}

	public function testComposeDependencyAndScope(): void {
		$result = $this->executeCodeSnippet(
			"composed = getFn1() * double; composed(5);",
			"T := Integer; ==> T :: T!2;",
			"
				getFn1 = ^ => (^Integer => Integer) :: { a = 3; ^x: Integer => Integer :: x + a; };
				double = ^x: Integer => Integer %% ~T :: x * t->value;"
		);
		$this->assertEquals("16", $result); // (5 + 3) * 2 = 16
	}



	// Result special cases
	public function testNoResult(): void {
		$result = $this->executeCodeSnippet(
			"fnC('good');",
			valueDeclarations: "
				fn1 = ^s: String => Integer<0..> :: s->length;
				fn2 = ^i: Integer<0..> => Real<0..> :: i->sqrt;
				fnC = ^s: String => Real<0..> :: {fn1 * fn2}(s);
			"
		);
		$this->assertEquals("2", $result);
	}

	public function testNoResultFn1(): void {
		$result = $this->executeCodeSnippet(
			"fnC('3');",
			valueDeclarations: "
				fn1 = ^s: String => *Integer :: s->asInteger *> ('invalid number');
				fn2 = ^i: Integer => Integer :: i + 2;
				fnC = ^s: String => *Integer :: {fn1 * fn2}(s);
			"
		);
		$this->assertEquals("5", $result);
	}

	public function testNoResultFn1Error(): void {
		$result = $this->executeCodeSnippet(
			"fnC('three');",
			valueDeclarations: "
				fn1 = ^s: String => *Integer :: s->asInteger *> ('invalid number');
				fn2 = ^i: Integer => Integer :: i + 2;
				fnC = ^s: String => *Integer :: {fn1 * fn2}(s);
			"
		);
		$this->assertEquals("@ExternalError[\n	errorType: 'NotANumber',\n	originalError: @NotANumber,\n	errorMessage: 'invalid number'\n]", $result);
	}

	public function testResultFn2(): void {
		$result = $this->executeCodeSnippet(
			"fnC('goodbye');",
			valueDeclarations: "
				fn1 = ^s: String => Integer<-3..> :: s->length - 3;
				fn2 = ^i: Integer<-3..> => *Real<0..> :: i->sqrt *> ('negative number');
				fnC = ^s: String => *Real<0..> :: {fn1 * fn2}(s);
			"
		);
		$this->assertEquals("2", $result);
	}

	public function testResultFn2Error(): void {
		$result = $this->executeCodeSnippet(
			"fnC('hi');",
			valueDeclarations: "
				fn1 = ^s: String => Integer<-3..> :: s->length - 3;
				fn2 = ^i: Integer<-3..> => *Real<0..> :: i->sqrt *> ('negative number');
				fnC = ^s: String => *Real<0..> :: {fn1 * fn2}(s);
			"
		);
		$this->assertEquals("@ExternalError[\n	errorType: 'NotANumber',\n	originalError: @NotANumber,\n	errorMessage: 'negative number'\n]", $result);
	}

	public function testResultSame(): void {
		$result = $this->executeCodeSnippet(
			"fnC('25');",
			valueDeclarations: "
				fn1 = ^s: String => *Integer :: s->asInteger *> ('invalid number');
				fn2 = ^i: Integer => *Real<0..> :: i->sqrt *> ('not a number');
				fnC = ^s: String => *Real<0..> :: {fn1 * fn2}(s);
			"
		);
		$this->assertEquals("5", $result);
	}

	public function testResultSameError1(): void {
		$result = $this->executeCodeSnippet(
			"fnC('twenty five');",
			valueDeclarations: "
				fn1 = ^s: String => *Integer :: s->asInteger *> ('invalid number');
				fn2 = ^i: Integer => *Real<0..> :: i->sqrt *> ('not a number');
				fnC = ^s: String => *Real<0..> :: {fn1 * fn2}(s);
			"
		);
		$this->assertEquals("@ExternalError[\n	errorType: 'NotANumber',\n	originalError: @NotANumber,\n	errorMessage: 'invalid number'\n]", $result);
	}

	public function testResultSameError2(): void {
		$result = $this->executeCodeSnippet(
			"fnC('-3');",
			valueDeclarations: "
				fn1 = ^s: String => *Integer :: s->asInteger *> ('invalid number');
				fn2 = ^i: Integer => *Real<0..> :: i->sqrt *> ('not a number');
				fnC = ^s: String => *Real<0..> :: {fn1 * fn2}(s);
			"
		);
		$this->assertEquals("@ExternalError[\n	errorType: 'NotANumber',\n	originalError: @NotANumber,\n	errorMessage: 'not a number'\n]", $result);
	}

	public function testResultAny(): void {
		$result = $this->executeCodeSnippet(
			"fnC('-3');",
			valueDeclarations: "
				fn1 = ^s: String => Any :: s->asInteger *> ('invalid number');
				fn2 = ^i: Any => *Real<0..> :: {i->as(`Integer) *> ('not an integer')}->sqrt *> ('not a number');
				fnC = ^s: String => *Real<0..> :: {fn1 * fn2}(s);
			"
		);
		$this->assertEquals("@ExternalError[\n	errorType: 'NotANumber',\n	originalError: @NotANumber,\n	errorMessage: 'not a number'\n]", $result);
	}

	public function testResultIncompatibleTypes(): void {
		$this->executeErrorCodeSnippet(
			"return type Result<Integer, HydrationError> of first function is not a subtype of parameter type Integer of second function",
			"fnC(25);",
			valueDeclarations: "
				fn1 = ^s: JsonValue => Result<Integer, HydrationError> :: s->hydrateAs(`Integer);
				fn2 = ^i: Integer => Result<Real<0..>, NotANumber> :: i->sqrt;
				fnC = ^s: JsonValue => Result<Real<0..>, HydrationError|NotANumber> :: {fn1 * fn2}(s);
			"
		);
	}

	public function testMixedResult(): void {
		$result = $this->executeCodeSnippet(
			"fnC('25');",
			"MyError := ();",
			"
				fn1 = ^s: JsonValue => *Result<Integer, NotANumber> :: s->hydrateAs(`String) *> ('Hydration failed')->asInteger;
				fn2 = ^i: Result<Integer, NotANumber> => Result<Integer, MyError> :: ?whenTypeOf(i) {
					`Integer: i + 5,
					`Error<NotANumber>: @MyError
				};
				fnC = ^s: JsonValue => *Result<Integer, MyError> :: {fn1 * fn2}(s);
			"
		);
		$this->assertEquals("30", $result);
	}

	public function testMixedResultNoExternalError(): void {
		$result = $this->executeCodeSnippet(
			"fnC('25');",
			"MyError := ();",
			"
				fn1 = ^s: String => Result<Integer> :: s->asInteger;
				fn2 = ^i: Result<Integer> => Result<Integer, MyError> :: ?whenTypeOf(i) {
					`Integer: i + 5,
					`Error: @MyError
				};
				fnC = ^s: String => Result<Integer> :: {fn1 * fn2}(s);
			"
		);
		$this->assertEquals("30", $result);
	}

	public function testMixedResultError1(): void {
		$result = $this->executeCodeSnippet(
			"fnC(true);",
			"MyError := ();",
			"
				fn1 = ^s: JsonValue => *Result<Integer, NotANumber> :: s->hydrateAs(`String) *> ('Hydration failed')->asInteger;
				fn2 = ^i: Result<Integer, NotANumber> => Result<Integer, MyError> :: ?whenTypeOf(i) {
					`Integer: i + 5,
					`Error<NotANumber>: @MyError
				};
				fnC = ^s: JsonValue => *Result<Integer, MyError> :: {fn1 * fn2}(s);
			"
		);
		$this->assertStringContainsString("ExternalError", $result);
	}

	public function testMixedResultError2(): void {
		$result = $this->executeCodeSnippet(
			"fnC('eight');",
			"MyError := ();",
			"
				fn1 = ^s: JsonValue => *Result<Integer, NotANumber> :: s->hydrateAs(`String) *> ('Hydration failed')->asInteger;
				fn2 = ^i: Result<Integer, NotANumber> => Result<Integer, MyError> :: ?whenTypeOf(i) {
					`Integer: i + 5,
					`Error<NotANumber>: @MyError
				};
				fnC = ^s: JsonValue => *Result<Integer, MyError> :: {fn1 * fn2}(s);
			"
		);
		$this->assertEquals("@MyError", $result);
	}


}
