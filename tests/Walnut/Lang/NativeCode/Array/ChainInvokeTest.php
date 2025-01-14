<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ChainInvokeTest extends CodeExecutionTestHelper {

	public function testChainInvokeEmpty(): void {
		$result = $this->executeCodeSnippet("[]->chainInvoke(5);");
		$this->assertEquals("5", $result);
	}

	public function testChainInvokeNonEmpty(): void {
		$result = $this->executeCodeSnippet("[
			^Real => Real :: #->roundAsInteger,
			^Real => Real :: # + 1,
			^Real => Real :: # * 2
		]->chainInvoke(3.74);");
		$this->assertEquals("10", $result);
	}

	/* not supported
	public function testChainInvokeNonEmptyError(): void {
		$result = $this->executeCodeSnippet("[1, 2, 5, 10, 5]->chainInvoke(^Integer => Result<Integer, String> :: @'error');");
		$this->assertEquals("@'error'", $result);
	}*/

	public function testChainInvokeInvalidTargetValue(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "[
			^Integer => Real :: #->roundAsInteger,
			^Real => Real :: # + 1,
			^Real => Real :: # * 2
		]->chainInvoke('hi');");
	}

	public function testChainInvokeInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "[
			15,
			^Real => Real :: # + 1,
			^Real => Real :: # * 2
		]->chainInvoke('hi');");
	}

	public function testChainInvokeInvalidParameterValue(): void {
		$this->executeErrorCodeSnippet("The parameter type String['hi'] is not a subtype of Real", "[
			^Real => Real :: #->roundAsInteger,
			^Real => Real :: # + 1,
			^Real => Real :: # * 2
		]->chainInvoke('hi');");
	}

}