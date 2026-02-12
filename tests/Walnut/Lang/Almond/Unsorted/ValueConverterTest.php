<?php

namespace Walnut\Lang\Test\Almond\Unsorted;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class ValueConverterTest extends CodeExecutionTestHelper {

	public function testBasicCastOk(): void {
		$result = $this->executeCodeSnippet("f[a: 1];", valueDeclarations: <<<NUT
		f = ^p :: p->as(`[a: Integer]);
	NUT);
		$this->assertEquals("[a: 1]", $result);
	}

	public function testBasicCastError(): void {
		$result = $this->executeCodeSnippet("f(1);", valueDeclarations: <<<NUT
		f = ^p :: p->as(`[a: Integer]);
	NUT);
		$this->assertEquals("@CastNotAvailable![\n\tfrom: type{Integer[1]},\n\tto: type[a: Integer]\n]", $result);
	}

}