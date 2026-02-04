<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class WithValuesTest extends CodeExecutionTestHelper {

	public function testWithValuesEnumeration(): void {
		$result = $this->executeCodeSnippet("type{MyEnumeration}->withValues[MyEnumeration.A, MyEnumeration.C];", "MyEnumeration := (A, B, C);");
		$this->assertEquals("type{MyEnumeration[A, C]}", $result);
	}

	public function testWithValuesEnumerationInvalidParameterType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"type{MyEnumeration}->withValues[MyEnumeration.A, OtherEnum.X];", "OtherEnum := (X); MyEnumeration := (A, B, C);");
	}

	public function testWithValuesEnumerationMetaType(): void {
		$result = $this->executeCodeSnippet(
			"getWithValues(type{MyEnumeration});",
			"MyEnumeration := (A, B, C);",
			"getWithValues = ^Type<Enumeration> => Result<Type<EnumerationSubset>, UnknownEnumerationValue> :: #->withValues[MyEnumeration.A, MyEnumeration.C];"
		);
		$this->assertEquals("type{MyEnumeration[A, C]}", $result);
	}

	public function testWithValuesEnumerationMetaTypeUnknown(): void {
		$result = $this->executeCodeSnippet(
			"getWithValues(type{MyEnumeration});",
			"OtherEnum := (X); MyEnumeration := (A, B, C);",
			"getWithValues = ^Type<Enumeration> => Result<Type<EnumerationSubset>, UnknownEnumerationValue> :: #->withValues[MyEnumeration.A, OtherEnum.X];"
		);
		$this->assertEquals("@UnknownEnumerationValue![\n\tenumeration: type{MyEnumeration},\n\tvalue: OtherEnum.X\n]", $result);
	}

	/*
	public function testWithValuesEnumerationSubset(): void {
		$result = $this->executeCodeSnippet("type{MyEnumeration[A, C]}->withValues[MyEnumeration.A];", "MyEnumeration := (A, B, C);");
		$this->assertEquals("type{MyEnumeration[A]}", $result);
	}*/

	/*public function testWithValuesEnumerationSubsetMetaType(): void {
		$result = $this->executeCodeSnippet("getWithValues(type{MyEnumeration[A, C]});",
			"MyEnumeration := (A, B, C); getWithValues = ^Type<EnumerationSubset> => Result<Type<EnumerationSubset>, UnknownEnumerationValue> :: #->withValues[MyEnumeration.A];");
		$this->assertEquals("type{MyEnumeration[A]}", $result);
	}*/

	public function testWithValuesString(): void {
		$result = $this->executeCodeSnippet("type{String}->withValues['A', 'C'];");
		$this->assertEquals("type{String['A', 'C']}", $result);
	}

	public function testWithValuesStringInvalidParameterType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"type{String}->withValues['A', 'C', 42];");
	}

	public function testWithValuesStringMetaType(): void {
		$result = $this->executeCodeSnippet("getWithValues(type{String});", valueDeclarations: "getWithValues = ^Type<String> => Type<StringSubset> :: #->withValues['A', 'C'];");
		$this->assertEquals("type{String['A', 'C']}", $result);
	}

	public function testWithValuesStringMetaTypeInvalidParameterType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"getWithValues(type{String});", valueDeclarations: "getWithValues = ^Type<String> => Type<StringSubset> :: #->withValues['A', 'C', 42];");
	}

	public function testWithValuesReal(): void {
		$result = $this->executeCodeSnippet("type{Real}->withValues[3.14, -2];");
		$this->assertEquals("type{Real[3.14, -2]}", $result);
	}

	public function testWithValuesRealInvalidParameterType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"type{Real}->withValues[3.14, -2, 'x'];");
	}

	public function testWithValuesRealMetaType(): void {
		$result = $this->executeCodeSnippet("getWithValues(type{Real});", valueDeclarations: "getWithValues = ^Type<Real> => Type<RealSubset> :: #->withValues[3.14, -2];");
		$this->assertEquals("type{Real[3.14, -2]}", $result);
	}

	public function testWithValuesRealMetaTypeInvalidParameterType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"getWithValues(type{Real});", valueDeclarations:  "getWithValues = ^Type<Real> => Type<RealSubset> :: #->withValues[3.14, -2, 'x'];");
	}

	public function testWithValuesInteger(): void {
		$result = $this->executeCodeSnippet("type{Integer}->withValues[42, -2];");
		$this->assertEquals("type{Integer[42, -2]}", $result);
	}

	public function testWithValuesIntegerInvalidParameterType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"type{Integer}->withValues[42, -2, 'x'];");
	}

	public function testWithValuesIntegerSubsetMetaType(): void {
		$result = $this->executeCodeSnippet("getWithValues(type{Integer});", valueDeclarations: "getWithValues = ^Type<Integer> => Type<IntegerSubset> :: #->withValues[42, -2];");
		$this->assertEquals("type{Integer[42, -2]}", $result);
	}

	public function testWithValuesIntegerMetaTypeInvalidParameterType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"getWithValues(type{Integer});", valueDeclarations:  "getWithValues = ^Type<Integer> => Type<IntegerSubset> :: #->withValues[42, -2, 'x'];");
	}

	public function testWithValuesInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "type{Array}->withValues[1, 2];");
	}

	public function testWithValuesMetaTypeInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type',
			"getWithValues(type[Integer]);", valueDeclarations:  "getWithValues = ^Type<Tuple> => Type :: #->withValues[1, 2];");
	}

}