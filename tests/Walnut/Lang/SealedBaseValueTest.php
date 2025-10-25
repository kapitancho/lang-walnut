<?php

namespace Walnut\Lang\Test;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class SealedBaseValueTest extends CodeExecutionTestHelper {
	public function test1(): void {
		$result = $this->executeCodeSnippet('t = S[a: 1, b: 2]; t->d;',
			'S := $[a: Integer, b: Integer]; S->d(=> Integer) :: $a;'
		);
		$this->assertEquals('1', $result);
	}
	public function test2(): void {
		$result = $this->executeCodeSnippet('t = S[a: 1, b: 2]; t->d;',
			'S := $[a: Integer, b: Integer]; S->d(=> [a: Integer, b: Integer]) :: $$;'
		);
		$this->assertEquals('[a: 1, b: 2]', $result);
	}
}