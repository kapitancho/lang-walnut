<?php

namespace Walnut\Lang\NativeCode\Map;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class FormatTest extends CodeExecutionTestHelper {

	public function testFormatEmpty(): void {
		$result = $this->executeCodeSnippet("[:]->format('No data');");
		$this->assertEquals("'No data'", $result);
	}

	public function testFormatSingleField(): void {
		$result = $this->executeCodeSnippet("[name: 'Alice']->format('Hello {name}!');");
		$this->assertEquals("'Hello Alice!'", $result);
	}

	public function testFormatMultipleFields(): void {
		$result = $this->executeCodeSnippet("[name: 'John', age: 25, active: true]->format('Name: {name}, Age: {age}, Active: {active}');");
		$this->assertEquals("'Name: John, Age: 25, Active: true'", $result);
	}

	public function testFormatWithIntegers(): void {
		$result = $this->executeCodeSnippet("[answer: 42, total: 100, lucky: 7]->format('Answer: {answer}, Total: {total}, Lucky: {lucky}');");
		$this->assertEquals("'Answer: 42, Total: 100, Lucky: 7'", $result);
	}

	public function testFormatWithReals(): void {
		$result = $this->executeCodeSnippet("[pi: 3.14, e: 2.71]->format('Pi: {pi}, E: {e}');");
		$this->assertEquals("'Pi: 3.14, E: 2.71'", $result);
	}

	public function testFormatWithMixedTypes(): void {
		$result = $this->executeCodeSnippet("[product: 'Widget', price: 19.99, quantity: 5, available: true]->format('{product} costs {price}, qty: {quantity}, available: {available}');");
		$this->assertEquals("'Widget costs 19.99, qty: 5, available: true'", $result);
	}

	public function testFormatUnusedFields(): void {
		$result = $this->executeCodeSnippet("[firstName: 'Alice', lastName: 'Smith', city: 'NYC']->format('Hello {firstName}');");
		$this->assertEquals("'Hello Alice'", $result);
	}

	public function testFormatNoPlaceholders(): void {
		$result = $this->executeCodeSnippet("[name: 'Alice', age: 30]->format('Hello World');");
		$this->assertEquals("'Hello World'", $result);
	}

	public function testFormatRepeatedPlaceholder(): void {
		$result = $this->executeCodeSnippet("[name: 'Alice']->format('{name} and {name} again');");
		$this->assertEquals("'Alice and Alice again'", $result);
	}

	public function testFormatComplexTemplate(): void {
		$result = $this->executeCodeSnippet("[firstName: 'Bob', lastName: 'Smith', city: 'New York']->format('{firstName} {lastName} lives in {city}');");
		$this->assertEquals("'Bob Smith lives in New York'", $result);
	}

	public function testFormatRecordOk(): void {
		$result = $this->executeCodeSnippet(
			"fmt[a: 1, b: 'hello']",
			valueDeclarations: "fmt = ^v: [a: Integer, b: String] => String :: v->format('{a} / {b}');"
		);
		$this->assertEquals("'1 / hello'", $result);
	}

	public function testFormatRecordTooFewArguments(): void {
		$result = $this->executeCodeSnippet(
			"fmt[a: 1, b: 'hello']",
			valueDeclarations: "fmt = ^v: [a: Integer, b: String] => Result<String, CannotFormatString> :: v->format('{a} / {b} / {c}');"
		);
		$this->assertEquals("@CannotFormatString![\n\tvalues: [a: 1, b: 'hello'],\n\tformat: '{a} / {b} / {c}'\n]", $result);
	}

	public function testFormatMapOk(): void {
		$result = $this->executeCodeSnippet(
			"fmt[a: 1, b: 'hello']",
			valueDeclarations: "fmt = ^v: Map<Integer|String> => Result<String, CannotFormatString> :: v->format('{a} / {b}');"
		);
		$this->assertEquals("'1 / hello'", $result);
	}

	public function testFormatMapMissing(): void {
		$result = $this->executeCodeSnippet(
			"fmt[a: 1, b: 'hello']",
			valueDeclarations: "fmt = ^v: Map<Integer|String> => Result<String, CannotFormatString> :: v->format('{a} / {c}');"
		);
		$this->assertEquals("@CannotFormatString![\n\tvalues: [a: 1, b: 'hello'],\n\tformat: '{a} / {c}'\n]", $result);
	}

	public function testFormatStringSubset(): void {
		$result = $this->executeCodeSnippet(
			"{D!'format 1: {a} / {b}'}->fmt[a: 1, b: 'hello']",
			"D := String['format 1: {a} / {b}', 'format 2: {a}', 'format 3: empty'];" .
			"D->fmt(^v: [a: Integer, b: String] => String) :: v->format($->value);"
		);
		$this->assertEquals("'format 1: 1 / hello'", $result);
	}

	public function testFormatStringSubsetUnsafe(): void {
		$result = $this->executeCodeSnippet(
			"{D!'format 1: {a} / {b}'}->fmt[a: 1, b: 'hello']",
			"D := String['format 1: {a} / {b}', 'format 2: {a} / {b} / {c}'];" .
			"D->fmt(^v: [a: Integer, b: String] => Result<String, CannotFormatString>) :: v->format($->value);"
		);
		$this->assertEquals("'format 1: 1 / hello'", $result);
	}

	public function testFormatStringSubsetUnsafeError(): void {
		$result = $this->executeCodeSnippet(
			"{D!'format 2: {a} / {b} / {c}'}->fmt[a: 1, b: 'hello']",
			"D := String['format 1: {a} / {b}', 'format 2: {a} / {b} / {c}'];" .
			"D->fmt(^v: [a: Integer, b: String] => Result<String, CannotFormatString>) :: v->format($->value);"
		);
		$this->assertEquals("@CannotFormatString![\n\tvalues: [a: 1, b: 'hello'],\n\tformat: 'format 2: {a} / {b} / {c}'\n]", $result);
	}

	public function testFormatInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[name: 'Alice']->format(123);");
	}

	public function testFormatInvalidTargetType(): void {
		self::markTestSkipped();
		$this->executeErrorCodeSnippet('is not a subtype of Shape<String>', "[data: [1, 2, 3]]->format('Test {data}');");
	}

}
