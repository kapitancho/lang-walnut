<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Set;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class FlipTest extends CodeExecutionTestHelper {

	public function testFlipEmpty(): void {
		$result = $this->executeCodeSnippet("[;]->flip;");
		$this->assertEquals("[:]", $result);
	}

	public function testFlipNonEmpty(): void {
		$result = $this->executeCodeSnippet("['1'; 'a']->flip;");
		$this->assertEquals("[1: 0, a: 1]", $result);
	}

	public function testFlipKeyType(): void {
		$result = $this->executeCodeSnippet(
			"fn['wx'; 'yz'];",
			valueDeclarations: "fn = ^m: Set<String<2>, ..3> => Map<String<2>:Integer<0..3>, ..3> :: m->flip;"
		);
		$this->assertEquals("[wx: 0, yz: 1]", $result);
	}

	public function testFlipMinLength(): void {
		$result = $this->executeCodeSnippet(
			"fn['wx'; 'yz'];",
			valueDeclarations: "fn = ^m: Set<String<2>, 2..3> => Map<String<2>:Integer<0..3>, 2..3> :: m->flip;"
		);
		$this->assertEquals("[wx: 0, yz: 1]", $result);
	}

	public function testFlipInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "[1; 'a']->flip;");
	}
}