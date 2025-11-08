<?php

namespace Walnut\Lang\Test\NativeCode\EventBus;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class FireTest extends CodeExecutionTestHelper {

	public function testFireWithInvalidTargetType(): void {
		$this->executeErrorCodeSnippet(
			'Cannot call method',
			"'not a bus'->fire['event-type', null];",
			typeDeclarations: "EventBus := \$[name: String];"
		);
	}

	public function testFireWithInvalidParameter(): void {
		$this->executeErrorCodeSnippet(
			'Cannot call method',
			"123->fire['event', null];",
			typeDeclarations: "EventBus := \$[name: String];"
		);
	}

}
