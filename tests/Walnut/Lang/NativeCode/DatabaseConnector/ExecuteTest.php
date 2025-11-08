<?php

namespace Walnut\Lang\Test\NativeCode\DatabaseConnector;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ExecuteTest extends CodeExecutionTestHelper {

	public function testExecuteWithInvalidTargetType(): void {
		$this->executeErrorCodeSnippet(
			'Cannot call method',
			"'not a connector'->execute['INSERT INTO test VALUES(1)'];",
			typeDeclarations: "DatabaseConnector := \$[connectionString: String];"
		);
	}

	public function testExecuteWithInvalidParameter(): void {
		$this->executeErrorCodeSnippet(
			'Cannot call method',
			"123->execute[42];",
			typeDeclarations: "DatabaseConnector := \$[connectionString: String];"
		);
	}

}
