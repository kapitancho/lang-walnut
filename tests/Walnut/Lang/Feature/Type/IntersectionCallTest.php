<?php

namespace Walnut\Lang\Feature\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class IntersectionCallTest extends CodeExecutionTestHelper {

	public function testIntersectionMethodCallOk(): void {
		$result = $this->executeCodeSnippet(
			"ab[a: 2, b: 5, c: 7];",
			<<<NUT
				A = Map<Real, 1..5>;
				B = Map<Integer, 3..7>;
				C = A&B;
				ab = ^val: C => Array :: val->values;
			NUT
		);
		$this->assertEquals("[2, 5, 7]", $result);
	}

	public function testIntersectionMethodCallMoreFunctions(): void {
		//"Error in global function ab : Cannot call method 'values' on type 'C': ambiguous method"
		$result = $this->executeCodeSnippet(
			"ab[a: 2, b: 'hello', c: 3.14];",
			<<<NUT
				A = [a: Integer, b: String, ...];
				A->myMethod(=> Real) :: 3.14;
				B = [a: Integer, c: Real, ...];
				B->myMethod(=> String) :: 'hello';
				C = A&B;
				ab = ^val: C => String :: val->myMethod;
			NUT
		);
		$this->assertEquals("'hello'", $result);
	}

}