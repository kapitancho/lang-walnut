<?php

namespace Walnut\Lang\Test\Almond\Unsorted;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class FunctionTest extends CodeExecutionTestHelper {

	public function testCustomMethodOnFunction(): void {
		$result = $this->executeCodeSnippet(
			"aqua = ^v => Fn :: aqua; aqua->x;",
			"
				Fn = ^ => \Fn;
				Fn->x(^v => Fn) :: $;
				Fn->item(^v => Fn) :: $;
			"
		);
		$this->assertEquals("^v: Any => Fn :: aqua", $result);
	}

	public function testCallCall(): void {
		$result = $this->executeCodeSnippet(
			"aqua()[]",
			"
				Fn = ^Null|[] => \Fn;
			",
			"aqua = ^v: Null|[] => Fn :: aqua;",
		);
		$this->assertEquals("^v: (Null|[]) => Fn :: aqua", $result);
	}

}