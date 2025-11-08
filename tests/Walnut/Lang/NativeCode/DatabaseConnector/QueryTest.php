<?php

namespace Walnut\Lang\Test\NativeCode\DatabaseConnector;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class QueryTest extends CodeExecutionTestHelper {

	public function testQueryWithInvalidTargetType(): void {
		$this->executeErrorCodeSnippet(
			'Cannot call method',
			"'not a connector'->query['SELECT 1'];",
			typeDeclarations: "DatabaseConnector := \$[connectionString: String];"
		);
	}

	public function testQueryWithInvalidParameter(): void {
		$this->executeErrorCodeSnippet(
			'Cannot call method',
			"123->query[42];",
			typeDeclarations: "DatabaseConnector := \$[connectionString: String];"
		);
	}

}
