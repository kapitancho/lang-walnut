<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\EventBus;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class FireTest extends CodeExecutionTestHelper {

	public function testFireOk(): void {
		$result = $this->executeCodeSnippet(
			"{ getEventBus()->fire(A); myCounter->value };",
			typeDeclarations: "
				A := ();
				B := ();
				EventListener = ^Nothing => *Null;
				EventBus := $[listeners: Array<EventListener>];
			",
			valueDeclarations: "
				myCounter = mutable{Array<String>, []};
				getEventBus = ^ => EventBus :: EventBus[listeners: [
					^A => Null :: { myCounter->PUSH('fn1'); null },
					^A|B => Null :: { myCounter->PUSH('fn2'); null },
					^B => Null :: { myCounter->PUSH('fn3'); null }
				]];
			"
		);
		$this->assertEquals("['fn1', 'fn2']", $result);
	}

	public function testFireErrorResult(): void {
		$result = $this->executeCodeSnippet(
			"{ getEventBus()->fire(A); myCounter->value };",
			typeDeclarations: "
				A := ();
				B := ();
				EventListener = ^Nothing => *Null;
				EventBus := $[listeners: Array<EventListener>];
			",
			valueDeclarations: "
				myCounter = mutable{Array<String>, []};
				getEventBus = ^ => EventBus :: EventBus[listeners: [
					^A => *Null :: { myCounter->PUSH('fn1'); {@'error'} *> ('An error occurred') },
					^A|B => Null :: { myCounter->PUSH('fn2'); null },
					^B => Null :: { myCounter->PUSH('fn3'); null }
				]];
			"
		);
		$this->assertEquals("['fn1']", $result);
	}

}
