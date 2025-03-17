<?php

namespace Walnut\Lang\Feature\Function;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class TupleAsRecordTest  extends CodeExecutionTestHelper {

	public function testGlobalFunctionCall(): void {
		$result = $this->executeCodeSnippet("fn[1, 3.14, 'hi'];", <<<NUT
		fn = ^[a: Integer, b: Real, c: String] => Real :: #a + #b + {#c->length};
	NUT);
		$this->assertEquals("6.14", $result);
	}

	public function testClosureFunctionCall(): void {
		$result = $this->executeCodeSnippet(<<<NUT
		fn = ^[a: Integer, b: Real, c: String] => Real :: #a + #b + {#c->length};
		fn[1, 3.14, 'hi'];
	NUT);
		$this->assertEquals("6.14", $result);
	}

	public function testMethodCall(): void {
		$result = $this->executeCodeSnippet("A()->fn[1, 3.14, 'hi'];", <<<NUT
		A = :[];
		A->fn(^[a: Integer, b: Real, c: String] => Real) :: #a + #b + {#c->length};
	NUT);
		$this->assertEquals("6.14", $result);
	}

	public function testOpenWithoutConstructorCall(): void {
		$result = $this->executeCodeSnippet("A[1, 'hi'];", <<<NUT
		A = #[a: Integer, b: String];
	NUT);
		$this->assertEquals("A[a: 1, b: 'hi']", $result);
	}

	public function testOpenWithInvariantConstructorCall(): void {
		$result = $this->executeCodeSnippet("A[1, 'hi'];", <<<NUT
		A = #[a: Integer, b: String] @ Any :: null;
	NUT);
		$this->assertEquals("A[a: 1, b: 'hi']", $result);
	}

	public function testOpenWithConstructorCall(): void {
		$result = $this->executeCodeSnippet("A['hi', 1];", <<<NUT
		A = #[a: Integer, b: String];
		A[f: String, e: Real] :: [a: #e->asInteger, b: #f];
	NUT);
		$this->assertEquals("A[a: 1, b: 'hi']", $result);
	}

	public function testOpenWithTwoConstructorsCall(): void {
		$result = $this->executeCodeSnippet("A['hi', 1];", <<<NUT
		A = #[a: Integer, b: String] @ Any :: null;
		A[f: String, e: Real] :: [a: #e->asInteger, b: #f];
	NUT);
		$this->assertEquals("A[a: 1, b: 'hi']", $result);
	}


	public function testSealedWithoutConstructorCall(): void {
		$result = $this->executeCodeSnippet("A[1, 'hi'];", <<<NUT
		A = $[a: Integer, b: String];
	NUT);
		$this->assertEquals("A[a: 1, b: 'hi']", $result);
	}

	public function testSealedWithInvariantConstructorCall(): void {
		$result = $this->executeCodeSnippet("A[1, 'hi'];", <<<NUT
		A = $[a: Integer, b: String] @ Any :: null;
	NUT);
		$this->assertEquals("A[a: 1, b: 'hi']", $result);
	}

	public function testSealedWithConstructorCall(): void {
		$result = $this->executeCodeSnippet("A['hi', 1];", <<<NUT
		A = $[a: Integer, b: String];
		A[f: String, e: Real] :: [a: #e->asInteger, b: #f];
	NUT);
		$this->assertEquals("A[a: 1, b: 'hi']", $result);
	}

	public function testSealedWithTwoConstructorsCall(): void {
		$result = $this->executeCodeSnippet("A['hi', 1];", <<<NUT
		A = $[a: Integer, b: String] @ Any :: null;
		A[f: String, e: Real] :: [a: #e->asInteger, b: #f];
	NUT);
		$this->assertEquals("A[a: 1, b: 'hi']", $result);
	}

}