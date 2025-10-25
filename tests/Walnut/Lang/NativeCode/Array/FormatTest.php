<?php

namespace Walnut\Lang\Test\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class FormatTest extends CodeExecutionTestHelper {

	public function testFormatEmpty(): void {
		$result = $this->executeCodeSnippet("[]->format('No items');");
		$this->assertEquals("'No items'", $result);
	}

	public function testFormatSingleElement(): void {
		$result = $this->executeCodeSnippet("['Alice']->format('Hello {0}!');");
		$this->assertEquals("'Hello Alice!'", $result);
	}

	public function testFormatMultipleElements(): void {
		$result = $this->executeCodeSnippet("['John', 25, true]->format('Name: {0}, Age: {1}, Active: {2}');");
		$this->assertEquals("'Name: John, Age: 25, Active: true'", $result);
	}

	public function testFormatWithIntegers(): void {
		$result = $this->executeCodeSnippet("[42, 100, 7]->format('Answer: {0}, Total: {1}, Lucky: {2}');");
		$this->assertEquals("'Answer: 42, Total: 100, Lucky: 7'", $result);
	}

	public function testFormatWithReals(): void {
		$result = $this->executeCodeSnippet("[3.14, 2.71]->format('Pi: {0}, E: {1}');");
		$this->assertEquals("'Pi: 3.14, E: 2.71'", $result);
	}

	public function testFormatWithMixedTypes(): void {
		$result = $this->executeCodeSnippet("['Product', 19.99, 5, true]->format('{0} costs {1}, qty: {2}, available: {3}');");
		$this->assertEquals("'Product costs 19.99, qty: 5, available: true'", $result);
	}

	public function testFormatMissingPlaceholder(): void {
		$result = $this->executeCodeSnippet("['Alice']->format('Hello {0}, your email is {1}');");
		$this->assertEquals("@CannotFormatString![\n\tvalues: ['Alice'],\n\tformat: 'Hello {0}, your email is {1}'\n]", $result);
	}

	public function testFormatUnusedElements(): void {
		$result = $this->executeCodeSnippet("['Alice', 'Bob', 'Charlie']->format('Hello {0}');");
		$this->assertEquals("'Hello Alice'", $result);
	}

	public function testFormatNoPlaceholders(): void {
		$result = $this->executeCodeSnippet("['Alice', 'Bob']->format('Hello World');");
		$this->assertEquals("'Hello World'", $result);
	}

	public function testFormatRepeatedPlaceholder(): void {
		$result = $this->executeCodeSnippet("['Alice']->format('{0} and {0} again');");
		$this->assertEquals("'Alice and Alice again'", $result);
	}

	public function testFormatTupleOk(): void {
		$result = $this->executeCodeSnippet(
			"fmt[1, 'hello']",
			valueDeclarations: "fmt = ^v: [Integer, String] => String :: v->format('{0} / {1}');"
		);
		$this->assertEquals("'1 / hello'", $result);
	}

	public function testFormatTupleTooFewArguments(): void {
		$result = $this->executeCodeSnippet(
			"fmt[1, 'hello']",
			valueDeclarations: "fmt = ^v: [Integer, String] => Result<String, CannotFormatString> :: v->format('{0} / {1} / {2}');"
		);
		$this->assertEquals("@CannotFormatString![\n\tvalues: [1, 'hello'],\n\tformat: '{0} / {1} / {2}'\n]", $result);
	}

	public function testFormatArrayNoMinLength(): void {
		$result = $this->executeCodeSnippet(
			"fmt[1, 'hello']",
			valueDeclarations: "fmt = ^v: Array<Integer|String> => Result<String, CannotFormatString> :: v->format('{0} / {1}');"
		);
		$this->assertEquals("'1 / hello'", $result);
	}

	public function testFormatArrayWithMinLength(): void {
		$result = $this->executeCodeSnippet(
			"fmt[1, 'hello', 42, -17]",
			valueDeclarations: "fmt = ^v: Array<Integer|String, 3..> => String :: v->format('{0} / {1}');"
		);
		$this->assertEquals("'1 / hello'", $result);
	}

	public function testFormatStringSubset(): void {
		$result = $this->executeCodeSnippet(
			"{D!'format 1: {0} / {1}'}->fmt[1, 'hello']",
			"D := String['format 1: {0} / {1}', 'format 2: {0}', 'format 3: empty'];" .
			"D->fmt(^v: [Integer, String] => String) :: v->format($->value);"
		);
		$this->assertEquals("'format 1: 1 / hello'", $result);
	}

	public function testFormatStringSubsetUnsafe(): void {
		$result = $this->executeCodeSnippet(
			"{D!'format 1: {0} / {1}'}->fmt[1, 'hello']",
			"D := String['format 1: {0} / {1}', 'format 2: {0} / {1} / {2}'];" .
			"D->fmt(^v: [Integer, String] => Result<String, CannotFormatString>) :: v->format($->value);"
		);
		$this->assertEquals("'format 1: 1 / hello'", $result);
	}

	public function testFormatStringSubsetUnsafeError(): void {
		$result = $this->executeCodeSnippet(
			"{D!'format 2: {0} / {1} / {2}'}->fmt[1, 'hello']",
			"D := String['format 1: {0} / {1}', 'format 2: {0} / {1} / {2}'];" .
			"D->fmt(^v: [Integer, String] => Result<String, CannotFormatString>) :: v->format($->value);"
		);
		$this->assertEquals("@CannotFormatString![\n\tvalues: [1, 'hello'],\n\tformat: 'format 2: {0} / {1} / {2}'\n]", $result);
	}

	public function testFormatInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "['a', 'b']->format(123);");
	}

	public function testFormatInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('is not a subtype of Shape<String>', "[[1, 2], [3, 4]]->format('Test {0}');");
	}

}
