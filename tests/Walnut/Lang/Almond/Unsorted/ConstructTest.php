<?php

namespace Walnut\Lang\Test\Almond\Unsorted;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class ConstructTest extends CodeExecutionTestHelper {

	public function testAtom(): void {
		$result = $this->executeCodeSnippet("A;", <<<NUT
		A := ();
	NUT);
		$this->assertEquals("A", $result);
	}

	public function testEnumKnownValue(): void {
		$result = $this->executeCodeSnippet("Suit('Spades');", <<<NUT
		Suit := (Spades, Hearts, Diamonds, Clubs);
	NUT);
		$this->assertEquals("Suit.Spades", $result);
	}

	public function testEnumKnownValueStringSubset(): void {
		$result = $this->executeCodeSnippet("getSuit('Spades');", <<<NUT
		Suit := (Spades, Hearts, Diamonds, Clubs);
	NUT, <<<NUT
		getSuit = ^v: String['Spades', 'Hearts', 'Diamonds', 'Clubs'] => Suit :: Suit(v);
	NUT);
		$this->assertEquals("Suit.Spades", $result);
	}

	public function testEnumKnownValueStringSubsetMissingValue(): void {
		$result = $this->executeCodeSnippet("getSuit('Spades');", <<<NUT
		Suit := (Spades, Hearts, Diamonds, Clubs);
	NUT, <<<NUT
		getSuit = ^v: String['Spades', 'Hearts', 'Diamonds', 'Clubs', 'Aces'] => Result<Suit, UnknownEnumerationValue> :: Suit(v);
	NUT);
		$this->assertEquals("Suit.Spades", $result);
	}

	public function testEnumKnownValueStringGeneral(): void {
		$result = $this->executeCodeSnippet("getSuit('Spades');", <<<NUT
		Suit := (Spades, Hearts, Diamonds, Clubs);
	NUT, <<<NUT
		getSuit = ^v: String => Result<Suit, UnknownEnumerationValue> :: Suit(v);
	NUT);
		$this->assertEquals("Suit.Spades", $result);
	}

	public function testEnumKnownValueStringGeneralWrongValue(): void {
		$result = $this->executeCodeSnippet("getSuit('King');", <<<NUT
		Suit := (Spades, Hearts, Diamonds, Clubs);
	NUT, <<<NUT
		getSuit = ^v: String => Result<Suit, UnknownEnumerationValue> :: Suit(v);
	NUT);
		$this->assertEquals("@UnknownEnumerationValue![enumeration: type{Suit}, value: 'King']", $result);
	}

	public function testEnumWithConstructorWithEnumValues(): void {
		$result = $this->executeCodeSnippet("Suit(2);", <<<NUT
		Suit := (Spades, Hearts, Diamonds, Clubs);
		Suit(i: Integer<1..4>) :: ?whenValueOf(i) {
			1: Suit.Spades,
			2: Suit.Hearts,
			3: Suit.Diamonds,
			4: Suit.Clubs
		};
	NUT);
		$this->assertEquals("Suit.Hearts", $result);
	}

	public function testEnumWithConstructorWithStrings(): void {
		$result = $this->executeCodeSnippet("Suit(2);", <<<NUT
		Suit := (Spades, Hearts, Diamonds, Clubs);
		Suit(i: Integer<1..4>) :: ?whenValueOf(i) {
			1: 'Spades',
			2: 'Hearts',
			3: 'Diamonds',
			4: 'Clubs'
		};
	NUT);
		$this->assertEquals("Suit.Hearts", $result);
	}

	public function testEnumWithConstructorWithStringsAndInvalidValueOk(): void {
		$result = $this->executeCodeSnippet("Suit(2);", <<<NUT
		Suit := (Spades, Hearts, Diamonds, Clubs);
		Suit(i: Integer<1..5>) :: ?whenValueOf(i) {
			1: 'Spades',
			2: 'Hearts',
			3: 'Diamonds',
			4: 'Clubs',
			5: 'King'
		};
	NUT);
		$this->assertEquals("Suit.Hearts", $result);
	}

	public function testEnumWithConstructorWithStringsAndInvalidValueWrongValue(): void {
		$result = $this->executeCodeSnippet("Suit(5);", <<<NUT
		Suit := (Spades, Hearts, Diamonds, Clubs);
		Suit(i: Integer<1..5>) :: ?whenValueOf(i) {
			1: 'Spades',
			2: 'Hearts',
			3: 'Diamonds',
			4: 'Clubs',
			5: 'King'
		};
	NUT);
		$this->assertEquals("@UnknownEnumerationValue![enumeration: type{Suit}, value: 'King']", $result);
	}



	public function testDataOk(): void {
		$result = $this->executeCodeSnippet("[A![a: 1, b: 'hi'], getA[a: 1, b: 'hi']];", <<<NUT
		A := [a: Integer, b: String];
	NUT, <<<NUT
		getA = ^p: [a: Integer, b: String] => A :: A!p;
	NUT);
		$this->assertEquals("[A![a: 1, b: 'hi'], A![a: 1, b: 'hi']]", $result);
	}

	public function testUnknownTypeConstructorError(): void {
		$this->executeErrorCodeSnippet(
			'The type "A" does not exist.',
			"A[a: 1, b: 'hi'];"
		);
	}

	public function testDataConstructorError(): void {
		$this->executeErrorCodeSnippet(
			"Construct expects a sealed, an open, or an enumeration type, A given",
			"A[a: 1, b: 'hi'];",
	<<<NUT
		A := [a: Integer, b: String];
	NUT);
	}

	public function testAliasConstructorError(): void {
		$this->executeErrorCodeSnippet(
			"Construct expects a sealed, an open, or an enumeration type, A given",
			"A[a: 1, b: 'hi'];",
	<<<NUT
		A = [a: Integer, b: String];
	NUT);
	}

	public function testOpenWithoutConstructorCallOk(): void {
		$result = $this->executeCodeSnippet("[A[a: 1, b: 'hi'], getA[a: 1, b: 'hi']];", <<<NUT
		A := #[a: Integer, b: String];
	NUT, <<<NUT
		getA = ^p: [a: Integer, b: String] => A :: A(p);
	NUT);
		$this->assertEquals("[A[a: 1, b: 'hi'], A[a: 1, b: 'hi']]", $result);
	}

	public function testOpenWithoutConstructorCallError(): void {
		$this->executeErrorCodeSnippet(
			"The constructor for type 'A' expects a parameter of type '[a: Integer, b: String]', but type '[a: Integer[1], other: String['hi']]' was provided.",
			"A[a: 1, other: 'hi'];",
		<<<NUT
			A := #[a: Integer, b: String];
		NUT);
	}

	public function testOpenWithInvariantConstructorCallOk(): void {
		$result = $this->executeCodeSnippet("[A[a: 1, b: 'hi'], getA[a: 1, b: 'hi']];", <<<NUT
		A := #[a: Integer, b: String] @ Any :: null;
	NUT, <<<NUT
		getA = ^p: [a: Integer, b: String] => Result<A, Any> :: A(p);
	NUT);
		$this->assertEquals("[A[a: 1, b: 'hi'], A[a: 1, b: 'hi']]", $result);
	}

	public function testOpenWithInvariantConstructorCallWrongReturnType(): void {
		$this->executeErrorCodeSnippet(
			"Function body return type 'Result<A, Any>' is not compatible with declared return type 'A'.",
			"[A[a: 1, b: 'hi'], getA[a: 1, b: 'hi']];",
		<<<NUT
			A := #[a: Integer, b: String] @ Any :: null;
		NUT,
		<<<NUT
			getA = ^p: [a: Integer, b: String] => A :: A(p);
		NUT);
	}

	public function testOpenWithInvariantConstructorCallOkErrorValue(): void {
		$result = $this->executeCodeSnippet("A[a: 1, b: 'hi'];", <<<NUT
		A := #[a: Integer, b: String] @ Any :: => @'error';
	NUT);
		$this->assertEquals("@'error'", $result);
	}

	public function testOpenWithInvariantConstructorCallError(): void {
		$this->executeErrorCodeSnippet(
			"The constructor for type 'A' expects a parameter of type '[a: Integer, b: String]', but type '[a: Integer[1], other: String['hi']]' was provided.",
			"A[a: 1, other: 'hi'];",
			<<<NUT
			A := #[a: Integer, b: String] @ Any :: null;
		NUT);
	}

	public function testOpenWithConstructorCallOk(): void {
		$result = $this->executeCodeSnippet("[A[f: 'hi', e: 1], getA[f: 'hi', e: 1]];", <<<NUT
		A := #[a: Integer, b: String];
		A[f: String, e: Real] :: [a: #e->asInteger, b: #f];
	NUT, <<<NUT
		getA = ^p: [f: String, e: Real] => A :: A(p);
	NUT);
		$this->assertEquals("[A[a: 1, b: 'hi'], A[a: 1, b: 'hi']]", $result);
	}

	public function testOpenWithConstructorCallOkErrorValue(): void {
		$result = $this->executeCodeSnippet("[A[f: 'hi', e: 1], getA[f: 'hi', e: 1]];", <<<NUT
		A := #[a: Integer, b: String];
		A[f: String, e: Real] @ Any :: @'error';
	NUT, <<<NUT
		getA = ^p: [f: String, e: Real] => Result<A, Any> :: A(p);
	NUT);
		$this->assertEquals("[@'error', @'error']", $result);
	}

	public function testOpenWithConstructorCallOkErrorValueWrongConstructionType(): void {
		$this->executeErrorCodeSnippet(
			"Function body return type 'Integer[15]' is not compatible with declared return type '[a: Integer, b: String]'.",
			"[A[f: 'hi', e: 1], getA[f: 'hi', e: 1]];",
		<<<NUT
			A := #[a: Integer, b: String];
			A[f: String, e: Real] :: 15;
		NUT);
	}

	public function testOpenWithConstructorCallOkErrorValueWrongReturnType(): void {
		$this->executeErrorCodeSnippet(
			"Function body return type 'Result<A, Any>' is not compatible with declared return type 'A'.",
			"[A[f: 'hi', e: 1], getA[f: 'hi', e: 1]];",
		<<<NUT
			A := #[a: Integer, b: String];
			A[f: String, e: Real] @ Any :: @'error';
		NUT,
		<<<NUT
			getA = ^p: [f: String, e: Real] => A :: A(p);
		NUT);
	}

	public function testOpenWithConstructorCallError(): void {
		$this->executeErrorCodeSnippet(
			"The parameter type '[f: String['hi'], other: Integer[3]]' is not compatible with declared parameter type '[f: String, e: Real]'.",
			"A[f: 'hi', other: 3];",
		<<<NUT
			A := #[a: Integer, b: String];
			A[f: String, e: Real] @ Any :: @'error';
		NUT);
	}

	public function testOpenWithTwoConstructorsCallOk(): void {
		$result = $this->executeCodeSnippet("A[f: 'hi', e: 1];", <<<NUT
		A := #[a: Integer, b: String] @ Any :: null;
		A[f: String, e: Real] :: [a: #e->asInteger, b: #f];
	NUT);
		$this->assertEquals("A[a: 1, b: 'hi']", $result);
	}

	public function testOpenWithTwoConstructorsCallErrorValueInvariant(): void {
		$result = $this->executeCodeSnippet("A[f: 'hi', e: 1];", <<<NUT
		A := #[a: Integer, b: String] @ Any :: => @'error';
		A[f: String, e: Real] :: [a: #e->asInteger, b: #f];
	NUT);
		$this->assertEquals("@'error'", $result);
	}

	public function testOpenWithTwoConstructorsCallErrorValueConstructor(): void {
		$result = $this->executeCodeSnippet("A[f: 'hi', e: 1];", <<<NUT
		A := #[a: Integer, b: String] @ Any :: null;
		A[f: String, e: Real] @ Any :: @'error';
	NUT);
		$this->assertEquals("@'error'", $result);
	}

	public function testOpenWithTwoConstructorsCallErrorValueBoth(): void {
		$result = $this->executeCodeSnippet("[A[f: 'hi', e: 1], getA[f: 'hi', e: 1]];", <<<NUT
		A := #[a: Integer, b: String] @ String['error 1'] :: @'error 1';
		A[f: String, e: Real] @ String['error 2'] :: @'error 2';
	NUT, <<<NUT
		getA = ^p: [f: String, e: Real] => Result<A, String['error 1', 'error 2']> :: A(p);
	NUT);
		$this->assertEquals("[@'error 2', @'error 2']", $result);
	}

	public function testOpenWithTwoConstructorsCallErrorValueBothWrongReturnType(): void {
		$this->executeErrorCodeSnippet(
			"Function body return type 'Result<A, String['error 2', 'error 1']>' is not compatible with declared return type 'A'.",
			"[A[f: 'hi', e: 1], getA[f: 'hi', e: 1]];",
		<<<NUT
			A := #[a: Integer, b: String] @ String['error 1'] :: @'error 1';
			A[f: String, e: Real] @ String['error 2'] :: @'error 2';
		NUT,
		<<<NUT
			getA = ^p: [f: String, e: Real] => A :: A(p);
		NUT);
	}

	public function testOpenWithTwoConstructorsCallError(): void {
		$this->executeErrorCodeSnippet(
			"The parameter type '[f: String['hi'], other: Integer[3]]' is not compatible with declared parameter type '[f: String, e: Real]'.",
			"A[f: 'hi', other: 3];",
		<<<NUT
			A := #[a: Integer, b: String] @ Any :: null;
			A[f: String, e: Real] :: [a: #e->asInteger, b: #f];
		NUT);
	}






	public function testSealedWithoutConstructorCallOk(): void {
		$result = $this->executeCodeSnippet("A[a: 1, b: 'hi'];", <<<NUT
		A := $[a: Integer, b: String];
	NUT);
		$this->assertEquals("A[a: 1, b: 'hi']", $result);
	}

	public function testSealedWithoutConstructorCallError(): void {
		$this->executeErrorCodeSnippet(
			"The constructor for type 'A' expects a parameter of type '[a: Integer, b: String]', but type '[a: Integer[1], other: String['hi']]' was provided.",
			"A[a: 1, other: 'hi'];",
			<<<NUT
			A := $[a: Integer, b: String];
		NUT);
	}

	public function testSealedWithInvariantConstructorCallOk(): void {
		$result = $this->executeCodeSnippet("A[a: 1, b: 'hi'];", <<<NUT
		A := $[a: Integer, b: String] @ Any :: null;
	NUT);
		$this->assertEquals("A[a: 1, b: 'hi']", $result);
	}

	public function testSealedWithInvariantConstructorCallOkErrorValue(): void {
		$result = $this->executeCodeSnippet("A[a: 1, b: 'hi'];", <<<NUT
		A := $[a: Integer, b: String] @ Any :: => @'error';
	NUT);
		$this->assertEquals("@'error'", $result);
	}

	public function testSealedWithInvariantConstructorCallError(): void {
		$this->executeErrorCodeSnippet(
			"The constructor for type 'A' expects a parameter of type '[a: Integer, b: String]', but type '[a: Integer[1], other: String['hi']]' was provided.",
			"A[a: 1, other: 'hi'];",
			<<<NUT
			A := $[a: Integer, b: String] @ Any :: null;
		NUT);
	}

	public function testSealedWithConstructorCallOk(): void {
		$result = $this->executeCodeSnippet("A[f: 'hi', e: 1];", <<<NUT
		A := $[a: Integer, b: String];
		A[f: String, e: Real] :: [a: #e->asInteger, b: #f];
	NUT);
		$this->assertEquals("A[a: 1, b: 'hi']", $result);
	}

	public function testSealedWithConstructorCallOkErrorValue(): void {
		$result = $this->executeCodeSnippet("A[f: 'hi', e: 1];", <<<NUT
		A := $[a: Integer, b: String];
		A[f: String, e: Real] @ Any :: @'error';
	NUT);
		$this->assertEquals("@'error'", $result);
	}

	public function testSealedWithConstructorCallError(): void {
		$this->executeErrorCodeSnippet(
			"The parameter type '[f: String['hi'], other: Integer[3]]' is not compatible with declared parameter type '[f: String, e: Real]'.",
			"A[f: 'hi', other: 3];",
			<<<NUT
			A := $[a: Integer, b: String];
			A[f: String, e: Real] @ Any :: @'error';
		NUT);
	}

	public function testSealedWithTwoConstructorsCallOk(): void {
		$result = $this->executeCodeSnippet("A[f: 'hi', e: 1];", <<<NUT
		A := $[a: Integer, b: String] @ Any :: null;
		A[f: String, e: Real] :: [a: #e->asInteger, b: #f];
	NUT);
		$this->assertEquals("A[a: 1, b: 'hi']", $result);
	}

	public function testSealedWithTwoConstructorsCallErrorValueInvariant(): void {
		$result = $this->executeCodeSnippet("A[f: 'hi', e: 1];", <<<NUT
		A := $[a: Integer, b: String] @ Any :: => @'error';
		A[f: String, e: Real] :: [a: #e->asInteger, b: #f];
	NUT);
		$this->assertEquals("@'error'", $result);
	}

	public function testSealedWithTwoConstructorsCallErrorValueConstructor(): void {
		$result = $this->executeCodeSnippet("A[f: 'hi', e: 1];", <<<NUT
		A := $[a: Integer, b: String] @ Any :: null;
		A[f: String, e: Real] @ Any :: @'error';
	NUT);
		$this->assertEquals("@'error'", $result);
	}

	public function testSealedWithTwoConstructorsCallErrorValueBoth(): void {
		$result = $this->executeCodeSnippet("A[f: 'hi', e: 1];", <<<NUT
		A := $[a: Integer, b: String] @ Any :: @'error 1';
		A[f: String, e: Real] @ Any :: @'error 2';
	NUT);
		$this->assertEquals("@'error 2'", $result);
	}

	public function testSealedWithTwoConstructorsCallError(): void {
		$this->executeErrorCodeSnippet(
			"The parameter type '[f: String['hi'], other: Integer[3]]' is not compatible with declared parameter type '[f: String, e: Real]'.",
			"A[f: 'hi', other: 3];",
			<<<NUT
			A := $[a: Integer, b: String] @ Any :: null;
			A[f: String, e: Real] :: [a: #e->asInteger, b: #f];
		NUT);
	}
}