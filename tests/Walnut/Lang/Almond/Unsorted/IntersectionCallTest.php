<?php

namespace Walnut\Lang\Test\Almond\Unsorted;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class IntersectionCallTest extends CodeExecutionTestHelper {

	public function testIntersectionMethodCallOk(): void {
		$result = $this->executeCodeSnippet(
			"ab[a: 2, b: 5, c: 7];",
			<<<NUT
				A = Map<Real, 1..5>;
				B = Map<Integer, 3..7>;
				C = A&B;
			NUT,
			<<<NUT
				ab = ^v: C => Array :: v->values;
			NUT
		);
		$this->assertEquals("[2, 5, 7]", $result);
	}

	public function testIntersectionMethodCallMoreFunctions(): void {
		$this->executeErrorCodeSnippet(
			"Error in method A->myMethod : the method myMethod is defined for both A and B but their target types are not compatible with each other",
			"ab[a: 2, b: 'hello', c: 3.14];",
			<<<NUT
				A = [a: Integer, b: String, ...];
				A->myMethod(=> Real) :: 3.14;
				B = [a: Integer, c: Real, ...];
				B->myMethod(=> String) :: 'hello';
				C = A&B;
			NUT,
			<<<NUT
				ab = ^v: C => String :: v->myMethod;
			NUT
		);
	}

}