<?php

namespace Walnut\Lang\Feature\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class UnionCallTest extends CodeExecutionTestHelper {

	public function testUnionMethodCallOk(): void {
		$result = $this->executeCodeSnippet(
			"[ab(A(3)), ab(B('hello'))];",
			<<<NUT
				A := #Integer;
				A->methodCall(=> Integer) :: $$ + 1;
				B := #String;
				B->methodCall(=> String) :: $$->reverse;
				ab = ^ val: A|B => Integer|String :: val->methodCall;
			NUT
		);
		$this->assertEquals("[4, 'olleh']", $result);
	}

	public function testUnionMethodCallWrongReturnType(): void {
		$this->executeErrorCodeSnippet(
			"expected a return value of type Boolean, got (Integer|String)",
			"[ab(A(3)), ab(B('hello'))];",
			<<<NUT
				A := #Integer;
				A->methodCall(=> Integer) :: $$ + 1;
				B := #String;
				B->methodCall(=> String) :: $$->reverse;
				ab = ^ val: A|B => Boolean :: val->methodCall;
			NUT
		);
	}

	public function testUnionMethodCallParameterOk(): void {
		$result = $this->executeCodeSnippet(
			"[ab(A(3)), ab(B('hello'))];",
			<<<NUT
				A := #Integer;
				A->methodCall(^p: Real => Real) :: $$ + p;
				B := #String;
				B->methodCall(^p: Integer => String) :: $$->reverse + {p->asString};
				ab = ^ val: A|B => Real|String :: val->methodCall(2);
			NUT
		);
		$this->assertEquals("[5, 'olleh2']", $result);
	}

	public function testUnionPropertyAccessOk(): void {
		$result = $this->executeCodeSnippet(
			"[ab(A[42, 'hello']), ab(B['hello', 3.14])];",
			<<<NUT
				A := #[a: Integer, b: String];
				B := #[a: String, c: Real];
				ab = ^ val: A|B => Integer|String :: val.a;
			NUT
		);
		$this->assertEquals("[42, 'hello']", $result);
	}

	public function testUnionPropertyAccessWrongReturnType(): void {
		$this->executeErrorCodeSnippet(
			"expected a return value of type Boolean, got (Integer|String)",
			"[ab(A[42, 'hello']), ab(B['hello', 3.14])];",
			<<<NUT
				A := #[a: Integer, b: String];
				B := #[a: String, c: Real];
				ab = ^ val: A|B => Boolean :: val.a;
			NUT
		);
	}

	public function testUnionPropertyAccessPartial(): void {
		$result = $this->executeCodeSnippet(
			"[ab(A[42, 'hello']), ab(B['hello', 3.14])];",
			<<<NUT
				A := #[a: Integer, b: String];
				B := #[a: String, c: Real];
				ab = ^ val: A|B => Result<String, MapItemNotFound> :: val.b;
			NUT
		);
		$this->assertEquals("['hello', @MapItemNotFound![key: 'b']]", $result);
	}

	public function testUnionPropertyAccessPartialRest(): void {
		$result = $this->executeCodeSnippet(
			"[ab(A[42, 'hello']), ab(B[a: 'hello', b: false, c: 3.14])];",
			<<<NUT
				A := #[a: Integer, b: String];
				B := #[a: String, c: Real, ... Boolean];
				ab = ^ val: A|B => Result<String|Boolean, MapItemNotFound> :: val.b;
			NUT
		);
		$this->assertEquals("['hello', false]", $result);
	}

	public function testUnionPropertyAccessSubset(): void {
		$result = $this->executeCodeSnippet(
			"v = A[42, 'hello']; [v->ab('a'), v->ab('b')];",
			<<<NUT
				A := #[a: Integer, b: String];
				A->ab(^ prop: String['a', 'b'] => Integer|String) :: $$->item(prop);
			NUT
		);
		$this->assertEquals("[42, 'hello']", $result);
	}

	public function testUnionPropertyAccessSubsetMayBeMissing(): void {
		$result = $this->executeCodeSnippet(
			"v = A[42, 'hello']; [v->ab('a'), v->ab('c')];",
			<<<NUT
				A := #[a: Integer, b: String];
				A->ab(^ prop: String['a', 'b', 'c'] => Result<Integer|String, MapItemNotFound>) :: $$->item(prop);
			NUT
		);
		$this->assertEquals("[42, @MapItemNotFound![key: 'c']]", $result);
	}

	public function testUnionPropertyAccessSubsetMayBeMissingRest(): void {
		$result = $this->executeCodeSnippet(
			"v = A[a: 42, b: 'hello', c: true]; [v->ab('a'), v->ab('c')];",
			<<<NUT
				A := #[a: Integer, b: String, ... Boolean];
				A->ab(^ prop: String['a', 'b', 'c'] => Result<Integer|String|Boolean, MapItemNotFound>) :: $$->item(prop);
			NUT
		);
		$this->assertEquals("[42, true]", $result);
	}

	public function testAllInOne(): void {
		$result = $this->executeCodeSnippet(
			"[fn[A[a: 42, b: 'hello'], 'a'], fn[B[b: 3.14, c: 'hello'], 'b']];",
			<<<NUT
				A := #[a: Integer, b: String];
				B := #[b: Real, c: String];
				fn = ^[p: A|B, v: String['a', 'b']] => Result<(String|Real), MapItemNotFound> :: #p->item(#v);
			NUT
		);
		$this->assertEquals("[42, 3.14]", $result);
	}

}