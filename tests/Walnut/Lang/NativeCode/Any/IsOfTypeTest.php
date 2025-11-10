<?php

namespace Walnut\Lang\Test\NativeCode\Any;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class IsOfTypeTest extends CodeExecutionTestHelper {
	public function testIsOfTypeNull(): void {
		$result = $this->executeCodeSnippet("null->isOfType(type{Null});");
		$this->assertEquals("true", $result);
	}

	public function testIsOfTypeTrue(): void {
		$result = $this->executeCodeSnippet("true->isOfType(type{True});");
		$this->assertEquals("true", $result);
	}

	public function testIsOfTypeTrueBoolean(): void {
		$result = $this->executeCodeSnippet("true->isOfType(type{Boolean});");
		$this->assertEquals("true", $result);
	}

	public function testIsOfTypeTrueFalse(): void {
		$result = $this->executeCodeSnippet("true->isOfType(type{False});");
		$this->assertEquals("false", $result);
	}

	public function testIsOfTypeFalse(): void {
		$result = $this->executeCodeSnippet("false->isOfType(type{False});");
		$this->assertEquals("true", $result);
	}

	public function testIsOfTypeFalseBoolean(): void {
		$result = $this->executeCodeSnippet("false->isOfType(type{Boolean});");
		$this->assertEquals("true", $result);
	}

	public function testIsOfTypeFalseTrue(): void {
		$result = $this->executeCodeSnippet("false->isOfType(type{True});");
		$this->assertEquals("false", $result);
	}

	public function testIsOfTypeInteger(): void {
		$result = $this->executeCodeSnippet("5->isOfType(type{Integer});");
		$this->assertEquals("true", $result);
	}

	public function testIsOfTypeIntegerInRange(): void {
		$result = $this->executeCodeSnippet("5->isOfType(type{Integer<3..7>});");
		$this->assertEquals("true", $result);
	}

	public function testIsOfTypeIntegerOutOfRange(): void {
		$result = $this->executeCodeSnippet("5->isOfType(type{Integer<13..17>});");
		$this->assertEquals("false", $result);
	}

	public function testIsOfTypeIntegerInSubset(): void {
		$result = $this->executeCodeSnippet("5->isOfType(type{Integer[1, 3, 5]});");
		$this->assertEquals("true", $result);
	}

	public function testIsOfTypeIntegerOutOfSubset(): void {
		$result = $this->executeCodeSnippet("5->isOfType(type{Integer[1, 3, 4]});");
		$this->assertEquals("false", $result);
	}

	public function testIsOfTypeReal(): void {
		$result = $this->executeCodeSnippet("5.5->isOfType(type{Real});");
		$this->assertEquals("true", $result);
	}

	public function testIsOfTypeRealInRange(): void {
		$result = $this->executeCodeSnippet("5.5->isOfType(type{Real<3.3..7.7>});");
		$this->assertEquals("true", $result);
	}

	public function testIsOfTypeRealOutOfRange(): void {
		$result = $this->executeCodeSnippet("5.5->isOfType(type{Real<13.3..17.7>});");
		$this->assertEquals("false", $result);
	}

	public function testIsOfTypeRealInSubset(): void {
		$result = $this->executeCodeSnippet("5.5->isOfType(type{Real[1.1, 3.3, 5.5]});");
		$this->assertEquals("true", $result);
	}

	public function testIsOfTypeRealOutOfSubset(): void {
		$result = $this->executeCodeSnippet("5.5->isOfType(type{Real[1.1, 3.3, 4.4]});");
		$this->assertEquals("false", $result);
	}

	public function testIsOfTypeString(): void {
		$result = $this->executeCodeSnippet("'Hello'->isOfType(type{String});");
		$this->assertEquals("true", $result);
	}

	public function testIsOfTypeStringInLengthRange(): void {
		$result = $this->executeCodeSnippet("'Hello'->isOfType(type{String<3..7>});");
		$this->assertEquals("true", $result);
	}

	public function testIsOfTypeStringOutOfLengthRange(): void {
		$result = $this->executeCodeSnippet("'Hello'->isOfType(type{String<13..17>});");
		$this->assertEquals("false", $result);
	}

	public function testIsOfTypeStringInSubset(): void {
		$result = $this->executeCodeSnippet("'Hello'->isOfType(type{String['Hello', 'World']});");
		$this->assertEquals("true", $result);
	}

	public function testIsOfTypeStringOutOfSubset(): void {
		$result = $this->executeCodeSnippet("'Hello'->isOfType(type{String['Welcome', 'World']});");
		$this->assertEquals("false", $result);
	}

	public function testIsOfTypeTuple(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3]->isOfType(type[Integer, Integer, Integer]);");
		$this->assertEquals("true", $result);
	}

	public function testIsOfTypeArray(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3]->isOfType(type{Array<Integer>});");
		$this->assertEquals("true", $result);
	}

	public function testIsOfTypeArrayInLengthRange(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3]->isOfType(type{Array<Integer, 3..5>});");
		$this->assertEquals("true", $result);
	}

	public function testIsOfTypeArrayOutOfLengthRange(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3]->isOfType(type{Array<Integer, 5..7>});");
		$this->assertEquals("false", $result);
	}

	public function testIsOfTypeRecord(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2]->isOfType(type[a: Integer, b: Integer]);");
		$this->assertEquals("true", $result);
	}

	public function testIsOfTypeMap(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2]->isOfType(type{Map<Integer>});");
		$this->assertEquals("true", $result);
	}

	public function testIsOfTypeMapInLengthRange(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2]->isOfType(type{Map<Integer, 2..3>});");
		$this->assertEquals("true", $result);
	}

	public function testIsOfTypeMapOutOfLengthRange(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2]->isOfType(type{Map<Integer, 3..4>});");
		$this->assertEquals("false", $result);
	}

	public function testIsOfTypeSet(): void {
		$result = $this->executeCodeSnippet("[1; 2; 3]->isOfType(type{Set<Integer>});");
		$this->assertEquals("true", $result);
	}

	public function testIsOfTypeSetInLengthRange(): void {
		$result = $this->executeCodeSnippet("[1; 2; 3]->isOfType(type{Set<Integer, 3..5>});");
		$this->assertEquals("true", $result);
	}

	public function testIsOfTypeSetOutOfLengthRange(): void {
		$result = $this->executeCodeSnippet("[1; 2; 3]->isOfType(type{Set<Integer, 5..7>});");
		$this->assertEquals("false", $result);
	}

	public function testIsOfTypeError(): void {
		$result = $this->executeCodeSnippet("{@'error'}->isOfType(type{Result<Nothing, String>});");
		$this->assertEquals("true", $result);
	}

	public function testIsOfTypeAtom(): void {
		$result = $this->executeCodeSnippet("{MyAtom}->isOfType(type{MyAtom});", "MyAtom := ();");
		$this->assertEquals("true", $result);
	}

	public function testIsOfTypeEnumeration(): void {
		$result = $this->executeCodeSnippet("{MyEnumeration.C}->isOfType(type{MyEnumeration});",
			"MyEnumeration := (A, B, C);");
		$this->assertEquals("true", $result);
	}

	public function testIsOfTypeEnumerationSubsetInRange(): void {
		$result = $this->executeCodeSnippet("{MyEnumeration.C}->isOfType(type{MyEnumeration[A, C]});",
			"MyEnumeration := (A, B, C);");
		$this->assertEquals("true", $result);
	}

	public function testIsOfTypeEnumerationSubsetOutOfRange(): void {
		$result = $this->executeCodeSnippet("{MyEnumeration.C}->isOfType(type{MyEnumeration[A, B]});",
			"MyEnumeration := (A, B, C);");
		$this->assertEquals("false", $result);
	}

	public function testIsOfTypeSealed(): void {
		$result = $this->executeCodeSnippet("{MySealed[a: 'value']}->isOfType(type{MySealed});", "MySealed := $[a: String];");
		$this->assertEquals("true", $result);
	}

	public function testIsOfTypeFunction(): void {
		$result = $this->executeCodeSnippet("{^Any => Integer :: 1}->isOfType(type{^Any => Integer});");
		$this->assertEquals("true", $result);
	}

	public function testIsOfTypeFunctionNoParameterTypeMatch(): void {
		$result = $this->executeCodeSnippet("{^String => Integer :: 1}->isOfType(type{^Real => Integer});");
		$this->assertEquals("false", $result);
	}

	public function testIsOfTypeFunctionNoReturnTypeMatch(): void {
		$result = $this->executeCodeSnippet("{^Any => Integer :: 1}->isOfType(type{^Any => String});");
		$this->assertEquals("false", $result);
	}

	public function testIsOfTypeMutable(): void {
		$result = $this->executeCodeSnippet("{mutable{Integer, 1}}->isOfType(type{Mutable<Integer>});");
		$this->assertEquals("true", $result);
	}

	public function testIsOfTypeMutableNotInType(): void {
		$result = $this->executeCodeSnippet("{mutable{Integer, 1}}->isOfType(type{Mutable<Real>});");
		$this->assertEquals("false", $result);
	}

	public function testIsOfTypeType(): void {
		$result = $this->executeCodeSnippet("type{Integer}->isOfType(type{Type<Integer>});");
		$this->assertEquals("true", $result);
	}

	public function testIsOfTypeTypeNotInType(): void {
		$result = $this->executeCodeSnippet("type{Integer}->isOfType(type{Type<String>});");
		$this->assertEquals("false", $result);
	}

	public function testIsOfTypeShape(): void {
		$result = $this->executeCodeSnippet(
			"[X->isOfType(`{String}), X->isOfType(`{Integer}), X->isOfType(`{String}&{Integer})];",
			"X := (); X ==> Integer :: 5; X ==> String :: 'hello';",
		);
		$this->assertEquals("[true, true, true]", $result);
	}

	public function testIsOfTypeInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type: Integer[42]",
			"type{Integer}->isOfType(42);"
		);
	}

}