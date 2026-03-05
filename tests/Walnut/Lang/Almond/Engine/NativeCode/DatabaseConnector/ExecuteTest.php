<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\DatabaseConnector;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class ExecuteTest extends CodeExecutionTestHelper {

	public function testExecute(): void {
		$result = $this->executeCodeSnippet(
			"{DatabaseConnector[connection: DatabaseConnection![dsn: 'sqlite::memory:']]}->execute[query: 'SELECT 1', boundParameters: []];",
			typeDeclarations: "
				DatabaseConnection := [dsn: String];
				DatabaseConnector := $[connection: DatabaseConnection];
				DatabaseSqlQuery = NonEmptyString;
				DatabaseValue = String|Integer|Real|Boolean|Null;
				DatabaseQueryDataRow = Map<DatabaseValue>;
				DatabaseQueryBoundParameters = Array<DatabaseValue>|DatabaseQueryDataRow;
				DatabaseQueryCommand = [query: DatabaseSqlQuery, boundParameters: DatabaseQueryBoundParameters];
				DatabaseQueryFailure := [query: DatabaseSqlQuery, boundParameters: DatabaseQueryBoundParameters, error: String];
			"
		);
		$this->assertEquals("0", $result);
	}

	public function testExecuteError(): void {
		$result = $this->executeCodeSnippet(
			"{DatabaseConnector[connection: DatabaseConnection![dsn: 'sqlite::memory:']]}->execute[query: 'SELECT', boundParameters: []];",
			typeDeclarations: "
				DatabaseConnection := [dsn: String];
				DatabaseConnector := $[connection: DatabaseConnection];
				DatabaseSqlQuery = NonEmptyString;
				DatabaseValue = String|Integer|Real|Boolean|Null;
				DatabaseQueryDataRow = Map<DatabaseValue>;
				DatabaseQueryBoundParameters = Array<DatabaseValue>|DatabaseQueryDataRow;
				DatabaseQueryCommand = [query: DatabaseSqlQuery, boundParameters: DatabaseQueryBoundParameters];
				DatabaseQueryFailure := [query: DatabaseSqlQuery, boundParameters: DatabaseQueryBoundParameters, error: String];
			"
		);
		$this->assertStringContainsString("@DatabaseQueryFailure", $result);
	}

	public function testExecuteWithInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Expected a parameter of type DatabaseQueryCommand, got String['SELECT 1']",
			"{DatabaseConnector[connection: DatabaseConnection![dsn: 'sqlite::memory:']]}->execute('SELECT 1');",
			"
				DatabaseConnection := [dsn: String];
				DatabaseConnector := $[connection: DatabaseConnection];
				DatabaseSqlQuery = NonEmptyString;
				DatabaseValue = String|Integer|Real|Boolean|Null;
				DatabaseQueryDataRow = Map<DatabaseValue>;
				DatabaseQueryBoundParameters = Array<DatabaseValue>|DatabaseQueryDataRow;
				DatabaseQueryCommand = [query: DatabaseSqlQuery, boundParameters: DatabaseQueryBoundParameters];
				DatabaseQueryFailure := [query: DatabaseSqlQuery, boundParameters: DatabaseQueryBoundParameters, error: String];
			"
		);
	}

}
