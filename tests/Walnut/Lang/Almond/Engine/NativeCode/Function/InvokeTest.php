<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Function;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

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
	NUT, <<<NUT
		fn = ^p: R&T => Real :: p(1);
	NUT);
		$this->assertEquals("null", $result);
	}

	public function testIntersectionNoMatch(): void {
		$this->executeErrorCodeSnippet(
			"Method 'invoke' is not defined for type '(R&T)'.",
			"null;",
		<<<NUT
		R := #[b: String];
		T = Null;
		NUT,
		<<<NUT
		fn = ^p: R&T => Real :: p(1);
		NUT);
	}

}