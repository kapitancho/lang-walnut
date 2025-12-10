<?php

namespace Walnut\Lang\Test\NativeCode\Array;

use PHPUnit\Framework\Attributes\DataProvider;
use Walnut\Lang\Test\CodeExecutionTestHelper;

final class DropTest extends CodeExecutionTestHelper {

	public function testDropEmpty(): void {
		$result = $this->executeCodeSnippet("[]->drop(5);");
		$this->assertEquals("[]", $result);
	}

	public function testDropZero(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3, 4, 5]->drop(0);");
		$this->assertEquals("[1, 2, 3, 4, 5]", $result);
	}

	public function testDropLessThanLength(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3, 4, 5]->drop(2);");
		$this->assertEquals("[3, 4, 5]", $result);
	}

	public function testDropExactLength(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3]->drop(3);");
		$this->assertEquals("[]", $result);
	}

	public function testDropMoreThanLength(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3]->drop(10);");
		$this->assertEquals("[]", $result);
	}

	public function testDropOne(): void {
		$result = $this->executeCodeSnippet("['hello', 'world', 'foo']->drop(1);");
		$this->assertEquals("['world', 'foo']", $result);
	}

	public static function dropParameterTypeProvider(): iterable {
		yield ['[arr: Array<String>, n: Integer<0..>] => Array<String>'];
		yield ['[arr: Array<String>, n: Integer<0..4>] => Array<String>'];
		yield ['[arr: Array<String, ..4>, n: Integer<0..>] => Array<String, ..4>'];
		yield ['[arr: Array<String, ..4>, n: Integer<1..3>] => Array<String, ..3>'];
		yield ['[arr: Array<String, 4..16>, n: Integer<1..3>] => Array<String, 1..15>'];
		yield ['[arr: Array<String, 3..6>, n: Integer<2..8>] => Array<String, ..4>'];
		yield ['[arr: Array<String, 2..8>, n: Integer<3..6>] => Array<String, ..5>'];

		yield ['[arr: [String, String, ... String], n: Integer<3..6>] => Array<String>'];
		yield ['[arr: [String, String, String, String], n: Integer<3..6>] => Array<String, ..1>'];
		yield ['[arr: [String, String, ... String], n: Integer[3]] => [... String]'];
		yield ['[arr: [String, String, String, String], n: Integer[3]] => [String]'];
	}

	#[DataProvider('dropParameterTypeProvider')]
	public function testTakeParameterType(string $expr): void {
		$result = $this->executeCodeSnippet(
			"take[['hello', 'world', 'foo', 'bar'], 3];",
			valueDeclarations: "
				take = ^$expr :: #arr->drop(#n);
			"
		);
		$this->assertEquals("['bar']", $result);
	}


	public function testDropParameterTypeTupleIn(): void {
		$result = $this->executeCodeSnippet(
			"drop[false, 'hello', 3.14, 2, -9.1];",
			valueDeclarations: "
				drop = ^arr: [Boolean, String, ...Real] => [String, ... Real] :: arr->drop(1);
			"
		);
		$this->assertEquals("['hello', 3.14, 2, -9.1]", $result);
	}

	public function testDropParameterTypeTupleOut(): void {
		$result = $this->executeCodeSnippet(
			"drop[false, 'hello', 3.14, 2, -9.1];",
			valueDeclarations: "
				drop = ^arr: [Boolean, String, ...Real] => [... Real] :: arr->drop(3);
			"
		);
		$this->assertEquals("[2, -9.1]", $result);
	}

	public function testDropParameterTypeTupleNoRest(): void {
		$result = $this->executeCodeSnippet(
			"drop[false, 'hello', 3.14, 2, -9.1];",
			valueDeclarations: "
				drop = ^arr: [Boolean, String, Real, Real, Real] => [Real, Real] :: arr->drop(3);
			"
		);
		$this->assertEquals("[2, -9.1]", $result);
	}

	public function testDropParameterTypeTupleInMore(): void {
		$result = $this->executeCodeSnippet(
			"drop[false, 'hello'];",
			valueDeclarations: "
				drop = ^arr: [Boolean, String] => [] :: arr->drop(3);
			"
		);
		$this->assertEquals("[]", $result);
	}

	public function testTakeParameterTypeZero(): void {
		$result = $this->executeCodeSnippet(
			"take[['hello', 'world', 'foo', 'bar'], 8];",
			valueDeclarations: "
				take = ^[arr: Array<String, 2..5>, n: Integer<7..10>] => Array<String, 0> :: #arr->drop(#n);
			"
		);
		$this->assertEquals("[]", $result);
	}

	public function testDropInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[1, 2, 3]->drop('hello');");
	}

	public function testDropNegativeNumber(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[1, 2, 3]->drop(-1);");
	}

}
