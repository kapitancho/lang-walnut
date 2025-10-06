<?php

namespace Walnut\Lang\Feature\Function;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class InvokeTest extends CodeExecutionTestHelper {

	public function testAlias(): void {
		$result = $this->executeCodeSnippet("fn(^i: Integer => Real :: i + 3.14);", <<<NUT
		T = ^Integer => Real;
	NUT, <<<NUT
		fn = ^p: T => Real :: p(1);
	NUT);
		$this->assertEquals("4.14", $result);
	}

	public function testIntersection(): void {
		$result = $this->executeCodeSnippet("null;", <<<NUT
		R = Shape<Real>;
		T = ^Integer => Real;
		fn = ^p: R&T => Real :: p(1);
	NUT);
		$this->assertEquals("null", $result);
	}

	public function testIntersectionNoMatch(): void {
		$this->executeErrorCodeSnippet(
			"Cannot call method 'invoke'",
			"null;",
		<<<NUT
		R := #[b: String];
		T = Null;
		fn = ^p: R&T => Real :: p(1);
		NUT);
	}

}