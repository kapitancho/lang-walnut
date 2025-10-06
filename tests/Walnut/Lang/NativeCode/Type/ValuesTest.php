<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ValuesTest extends CodeExecutionTestHelper {

	public function testValuesEnumeration(): void {
		$result = $this->executeCodeSnippet("type{MyEnumeration}->values;", "MyEnumeration := (A, B);");
		$this->assertEquals("[MyEnumeration.A, MyEnumeration.B]", $result);
	}

	public function testValuesEnumerationMetaType(): void {
		$result = $this->executeCodeSnippet(
			"getValues(type{MyEnumeration});",
			"MyEnumeration := (A, B);",
			"getValues = ^Type<Enumeration> => Array<1..> :: #->values;"
		);
		$this->assertEquals("[MyEnumeration.A, MyEnumeration.B]", $result);
	}

	public function testValuesEnumerationSubset(): void {
		$result = $this->executeCodeSnippet("type{MyEnumeration[A, C]}->values;", "MyEnumeration := (A, B, C);");
		$this->assertEquals("[MyEnumeration.A, MyEnumeration.C]", $result);
	}

	public function testValuesEnumerationSubsetMetaType(): void {
		$result = $this->executeCodeSnippet(
			"getValues(type{MyEnumeration[A, C]});",
			"MyEnumeration := (A, B, C);",
			"getValues = ^Type<EnumerationSubset> => Array<1..> :: #->values;");
		$this->assertEquals("[MyEnumeration.A, MyEnumeration.C]", $result);
	}

	public function testValuesStringSubset(): void {
		$result = $this->executeCodeSnippet("type{String['A', 'C']}->values;");
		$this->assertEquals("['A', 'C']", $result);
	}

	public function testValuesStringSubsetMetaType(): void {
		$result = $this->executeCodeSnippet(
			"getValues(type{String['A', 'C']});",
			valueDeclarations: "getValues = ^Type<StringSubset> => Array<String, 1..> :: #->values;",
		);
		$this->assertEquals("['A', 'C']", $result);
	}

	public function testValuesRealSubset(): void {
		$result = $this->executeCodeSnippet("type{Real[3.14, -2]}->values;");
		$this->assertEquals("[3.14, -2]", $result);
	}

	public function testValuesRealSubsetMetaType(): void {
		$result = $this->executeCodeSnippet("getValues(type{Real[3.14, -2]});", valueDeclarations: "getValues = ^Type<RealSubset> => Array<Real, 1..> :: #->values;");
		$this->assertEquals("[3.14, -2]", $result);
	}

	public function testValuesIntegerSubset(): void {
		$result = $this->executeCodeSnippet("type{Integer[42, -2]}->values;");
		$this->assertEquals("[42, -2]", $result);
	}

	public function testValuesIntegerSubsetMetaType(): void {
		$result = $this->executeCodeSnippet("getValues(type{Integer[42, -2]});", valueDeclarations: "getValues = ^Type<IntegerSubset> => Array<Integer, 1..> :: #->values;");
		$this->assertEquals("[42, -2]", $result);
	}

	public function testValuesInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type',
			"type{String}->values;");
	}

	public function testValuesInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type',
			"type{String['A', 'C']}->values(42);");
	}

	public function testValuesMetaTypeInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type',
			"getValues(type[Integer]);", "getValues = ^Type<Tuple> => Array<Integer, 1..> :: #->values;");
	}

}