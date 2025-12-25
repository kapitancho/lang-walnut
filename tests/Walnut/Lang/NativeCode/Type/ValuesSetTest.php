<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ValuesSetTest extends CodeExecutionTestHelper {

	public function testValuesEnumeration(): void {
		$result = $this->executeCodeSnippet("type{MyEnumeration}->valuesSet;", "MyEnumeration := (A, B);");
		$this->assertEquals("[MyEnumeration.A; MyEnumeration.B]", $result);
	}

	public function testValuesEnumerationMetaType(): void {
		$result = $this->executeCodeSnippet(
			"getValues(type{MyEnumeration});",
			"MyEnumeration := (A, B);",
			"getValues = ^Type<Enumeration> => Set<1..> :: #->valuesSet;"
		);
		$this->assertEquals("[MyEnumeration.A; MyEnumeration.B]", $result);
	}

	public function testValuesEnumerationSubset(): void {
		$result = $this->executeCodeSnippet("type{MyEnumeration[A, C]}->valuesSet;", "MyEnumeration := (A, B, C);");
		$this->assertEquals("[MyEnumeration.A; MyEnumeration.C]", $result);
	}

	public function testValuesEnumerationSubsetMetaType(): void {
		$result = $this->executeCodeSnippet(
			"getValues(type{MyEnumeration[A, C]});",
			"MyEnumeration := (A, B, C);",
			"getValues = ^Type<EnumerationSubset> => Set<1..> :: #->valuesSet;");
		$this->assertEquals("[MyEnumeration.A; MyEnumeration.C]", $result);
	}

	public function testValuesStringSubset(): void {
		$result = $this->executeCodeSnippet("type{String['A', 'C']}->valuesSet;");
		$this->assertEquals("['A'; 'C']", $result);
	}

	public function testValuesStringSubsetMetaType(): void {
		$result = $this->executeCodeSnippet(
			"getValues(type{String['A', 'C']});",
			valueDeclarations: "getValues = ^Type<StringSubset> => Set<String, 1..> :: #->valuesSet;",
		);
		$this->assertEquals("['A'; 'C']", $result);
	}

	public function testValuesRealSubset(): void {
		$result = $this->executeCodeSnippet("type{Real[3.14, -2]}->valuesSet;");
		$this->assertEquals("[3.14; -2]", $result);
	}

	public function testValuesRealSubsetMetaType(): void {
		$result = $this->executeCodeSnippet("getValues(type{Real[3.14, -2]});", valueDeclarations: "getValues = ^Type<RealSubset> => Set<Real, 1..> :: #->valuesSet;");
		$this->assertEquals("[3.14; -2]", $result);
	}

	public function testValuesIntegerSubset(): void {
		$result = $this->executeCodeSnippet("type{Integer[42, -2]}->valuesSet;");
		$this->assertEquals("[42; -2]", $result);
	}

	public function testValuesIntegerSubsetMetaType(): void {
		$result = $this->executeCodeSnippet("getValues(type{Integer[42, -2]});", valueDeclarations: "getValues = ^Type<IntegerSubset> => Set<Integer, 1..> :: #->valuesSet;");
		$this->assertEquals("[42; -2]", $result);
	}

	public function testValuesInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type',
			"type{String}->valuesSet;");
	}

	public function testValuesInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type',
			"type{String['A', 'C']}->valuesSet(42);");
	}

	public function testValuesMetaTypeInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type',
			"getValues(type[Integer]);", valueDeclarations:  "getValues = ^Type<Tuple> => Set<Integer, 1..> :: #->valuesSet;");
	}

}