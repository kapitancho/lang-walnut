<?php

namespace Walnut\Lang\Test\Implementation\NativeCode\Any;

use Walnut\Lang\Test\Implementation\BaseProgramTestHelper;

final class BinaryNotEqualTest extends BaseProgramTestHelper {

	public function testBinaryEqual(): void {
		$c1 = $this->expressionRegistry->constant($this->valueRegistry->integer(123));
		$c2 = $this->expressionRegistry->constant($this->valueRegistry->integer(456));
		$c = $this->expressionRegistry->constant($this->valueRegistry->integer(123));

		$this->testMethodCall($c, 'binaryNotEqual',$c1, $this->valueRegistry->false);
		$this->testMethodCall($c, 'binaryNotEqual', $c2, $this->valueRegistry->true);
	}
}
