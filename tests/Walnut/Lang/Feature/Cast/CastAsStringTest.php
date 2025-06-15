<?php

namespace Walnut\Lang\Feature\Cast;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class CastAsStringTest extends CodeExecutionTestHelper {

	public function testAsStringWithoutExistingCast(): void {
		$result = $this->executeCodeSnippet("getStringA(A[42, 'Hello']);", <<<NUT
		A := #[a: Integer, b: String];
		getStringA = ^p: A => Result<String, CastNotAvailable> :: p->as(type{String});
	NUT);
		$this->assertEquals("@CastNotAvailable!!!!![from: type{A}, to: type{String}]", $result);
	}

	public function testAsStringWithExistingCast(): void {
		$result = $this->executeCodeSnippet("getStringA(A[42, 'Hello']);", <<<NUT
		A := #[a: Integer, b: String];
		A ==> String :: \$b;
		getStringA = ^p: A => String :: p->as(type{String});
	NUT);
		$this->assertEquals("'Hello'", $result);
	}

}