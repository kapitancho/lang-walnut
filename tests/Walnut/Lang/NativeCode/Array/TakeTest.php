<?php

namespace Walnut\Lang\Test\NativeCode\Array;

use PHPUnit\Framework\Attributes\DataProvider;
use Walnut\Lang\Test\CodeExecutionTestHelper;

final class TakeTest extends CodeExecutionTestHelper {

	public function testTakeEmpty(): void {
		$result = $this->executeCodeSnippet("[]->take(5);");
		$this->assertEquals("[]", $result);
	}

	public function testTakeZero(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3, 4, 5]->take(0);");
		$this->assertEquals("[]", $result);
	}

	public function testTakeLessThanLength(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3, 4, 5]->take(3);");
		$this->assertEquals("[1, 2, 3]", $result);
	}

	public function testTakeExactLength(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3]->take(3);");
		$this->assertEquals("[1, 2, 3]", $result);
	}

	public function testTakeMoreThanLength(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3]->take(10);");
		$this->assertEquals("[1, 2, 3]", $result);
	}

	public function testTakeOne(): void {
		$result = $this->executeCodeSnippet("['hello', 'world', 'foo']->take(1);");
		$this->assertEquals("['hello']", $result);
	}

	public static function takeParameterTypeProvider(): iterable {
		yield ['[arr: Array<String>, n: Integer<0..>] => Array<String>'];
		yield ['[arr: Array<String>, n: Integer<0..4>] => Array<String, ..4>'];
		yield ['[arr: Array<String, ..4>, n: Integer<0..>] => Array<String, ..4>'];
		yield ['[arr: Array<String, ..4>, n: Integer<0..3>] => Array<String, ..3>'];
		yield ['[arr: Array<String, 3..6>, n: Integer<2..8>] => Array<String, 2..6>'];
		yield ['[arr: Array<String, 2..8>, n: Integer<3..6>] => Array<String, 2..6>'];
		yield ['[arr: [String, String, ... String], n: Integer<3..6>] => Array<String, 2..>'];
		yield ['[arr: [String, String, String, String], n: Integer<3..6>] => Array<String, 3..4>'];
		yield ['[arr: [String, String, ... String], n: Integer[3]] => [String, String, ... String]'];
		yield ['[arr: [String, String, String, String], n: Integer[3]] => [String, String, String]'];
	}

	#[DataProvider('takeParameterTypeProvider')]
	public function testTakeParameterType(string $expr): void {
		$result = $this->executeCodeSnippet(
			"take[['hello', 'world', 'foo', 'bar'], 3];",
			valueDeclarations: "
				take = ^$expr :: #arr->take(#n);
			"
		);
		$this->assertEquals("['hello', 'world', 'foo']", $result);
	}

	public function testTakeParameterTypeTupleIn(): void {
		$result = $this->executeCodeSnippet(
			"take[false, 'hello', 3.14, 2, -9.1];",
			valueDeclarations: "
				take = ^arr: [Boolean, String, ...Real] => [Boolean, String] :: arr->take(2);
			"
		);
		$this->assertEquals("[false, 'hello']", $result);
	}

	public function testTakeParameterTypeTupleOut(): void {
		$result = $this->executeCodeSnippet(
			"take[false, 'hello', 3.14, 2, -9.1];",
			valueDeclarations: "
				take = ^arr: [Boolean, String, ...Real] => [Boolean, String, ...Real] :: arr->take(3);
			"
		);
		$this->assertEquals("[false, 'hello', 3.14]", $result);
	}

	public function testTakeParameterTypeTupleInMore(): void {
		$result = $this->executeCodeSnippet(
			"take[false, 'hello'];",
			valueDeclarations: "
				take = ^arr: [Boolean, String] => [Boolean, String] :: arr->take(3);
			"
		);
		$this->assertEquals("[false, 'hello']", $result);
	}

	public function testTakeInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[1, 2, 3]->take('hello');");
	}

	public function testTakeNegativeNumber(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[1, 2, 3]->take(-1);");
	}

}
