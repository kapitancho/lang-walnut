<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class ValueWithNameTest extends CodeExecutionTestHelper {

	public function testValueWithNameEnumeration(): void {
		$result = $this->executeCodeSnippet("type{MyEnumeration}->valueWithName('A');", "MyEnumeration := (A, B);");
		$this->assertEquals("MyEnumeration.A", $result);
	}

	public function testValueWithNameEnumerationUnknown(): void {
		$result = $this->executeCodeSnippet("type{MyEnumeration}->valueWithName('Q');", "MyEnumeration := (A, B);");
		$this->assertEquals("@UnknownEnumerationValue![\n\tenumeration: type{MyEnumeration},\n\tvalue: 'Q'\n]", $result);
	}

	public function testValueWithNameEnumerationMetaType(): void {
		$result = $this->executeCodeSnippet(
			"getValueWithName(type{MyEnumeration});",
			"MyEnumeration := (A, B);",
			"getValueWithName = ^Type<Enumeration> => Result<Any, UnknownEnumerationValue> :: #->valueWithName('A');"
		);
		$this->assertEquals("MyEnumeration.A", $result);
	}

	public function testValueWithNameEnumerationMetaTypeUnknown(): void {
		$result = $this->executeCodeSnippet(
			"getValueWithName(type{MyEnumeration});",
			"MyEnumeration := (A, B);",
			"getValueWithName = ^Type<Enumeration> => Result<Any, UnknownEnumerationValue> :: #->valueWithName('Q');"
		);
		$this->assertEquals("@UnknownEnumerationValue![\n\tenumeration: type{MyEnumeration},\n\tvalue: 'Q'\n]", $result);
	}

	public function testValueWithNameEnumerationSubset(): void {
		$result = $this->executeCodeSnippet("type{MyEnumeration[A, C]}->valueWithName('A');", "MyEnumeration := (A, B, C);");
		$this->assertEquals("MyEnumeration.A", $result);
	}

	public function testValueWithNameEnumerationSubsetUnknown(): void {
		$result = $this->executeCodeSnippet("type{MyEnumeration[A, C]}->valueWithName('B');", "MyEnumeration := (A, B, C);");
		$this->assertEquals("@UnknownEnumerationValue![\n\tenumeration: type{MyEnumeration[A, C]},\n\tvalue: 'B'\n]", $result);
	}

	public function testValueWithNameEnumerationSubsetMetaType(): void {
		$result = $this->executeCodeSnippet(
			"getValueWithName(type{MyEnumeration[A, C]});",
			"MyEnumeration := (A, B, C); ",
			"getValueWithName = ^Type<EnumerationSubset> => Result<Any, UnknownEnumerationValue> :: #->valueWithName('A');"
		);
		$this->assertEquals("MyEnumeration.A", $result);
	}

	public function testValueWithNameEnumerationSubsetMetaTypeUnknown(): void {
		$result = $this->executeCodeSnippet(
			"getValueWithName(type{MyEnumeration[A, C]});",
			"MyEnumeration := (A, B, C);",
			"getValueWithName = ^Type<EnumerationSubset> => Result<Any, UnknownEnumerationValue> :: #->valueWithName('B');"
		);
		$this->assertEquals("@UnknownEnumerationValue![\n\tenumeration: type{MyEnumeration[A, C]},\n\tvalue: 'B'\n]", $result);
	}

	public function testValueWithNameInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type',
			"type{MyEnumeration}->valueWithName(4);", "MyEnumeration := (A, B);");
	}

}