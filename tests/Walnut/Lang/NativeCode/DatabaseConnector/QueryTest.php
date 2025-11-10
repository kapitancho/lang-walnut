<?php

namespace Walnut\Lang\Test\NativeCode\DatabaseConnector;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class QueryTest extends CodeExecutionTestHelper {


	public function testExecute(): void {
		$result = $this->executeCodeSnippet(
			"{DatabaseConnector[connection: DatabaseConnection![dsn: 'sqlite::memory:']]}->query[query: 'SELECT 1 AS id, 3.14 AS r, \`hello\` AS str, null AS n', boundParameters: []];",
			typeDeclarations: "
				DatabaseConnection := [dsn: String];
				DatabaseConnector := $[connection: DatabaseConnection];
				DatabaseSqlQuery = NonEmptyString;
				DatabaseValue = String|Integer|Real|Boolean|Null;
				DatabaseQueryDataRow = Map<DatabaseValue>;
				DatabaseQueryBoundParameters = Array<DatabaseValue>|DatabaseQueryDataRow;
				DatabaseQueryCommand = [query: DatabaseSqlQuery, boundParameters: DatabaseQueryBoundParameters];
				DatabaseQueryResultRow = Map<DatabaseValue>;
				DatabaseQueryResult = Array<DatabaseQueryResultRow>;				
				DatabaseQueryFailure := [query: DatabaseSqlQuery, boundParameters: DatabaseQueryBoundParameters, error: String];
			"
		);
		$this->assertEquals("[\n	[id: 1, r: 3.14, str: 'hello', n: null]\n]", $result);
	}

	public function testQueryError(): void {
		$result = $this->executeCodeSnippet(
			"{DatabaseConnector[connection: DatabaseConnection![dsn: 'sqlite::memory:']]}->query[query: 'SELECT', boundParameters: []];",
			typeDeclarations: "
				DatabaseConnection := [dsn: String];
				DatabaseConnector := $[connection: DatabaseConnection];
				DatabaseSqlQuery = NonEmptyString;
				DatabaseValue = String|Integer|Real|Boolean|Null;
				DatabaseQueryDataRow = Map<DatabaseValue>;
				DatabaseQueryBoundParameters = Array<DatabaseValue>|DatabaseQueryDataRow;
				DatabaseQueryCommand = [query: DatabaseSqlQuery, boundParameters: DatabaseQueryBoundParameters];
				DatabaseQueryResultRow = Map<DatabaseValue>;
				DatabaseQueryResult = Array<DatabaseQueryResultRow>;				
				DatabaseQueryFailure := [query: DatabaseSqlQuery, boundParameters: DatabaseQueryBoundParameters, error: String];
			"
		);
		$this->assertStringContainsString("@DatabaseQueryFailure", $result);
	}


	public function testQueryWithInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type: String['SELECT 1']",
			"{DatabaseConnector[connection: DatabaseConnection![dsn: 'sqlite::memory:']]}->query('SELECT 1');",
			"
				DatabaseConnection := [dsn: String];
				DatabaseConnector := $[connection: DatabaseConnection];
				DatabaseSqlQuery = NonEmptyString;
				DatabaseValue = String|Integer|Real|Boolean|Null;
				DatabaseQueryDataRow = Map<DatabaseValue>;
				DatabaseQueryBoundParameters = Array<DatabaseValue>|DatabaseQueryDataRow;
				DatabaseQueryCommand = [query: DatabaseSqlQuery, boundParameters: DatabaseQueryBoundParameters];
				DatabaseQueryResultRow = Map<DatabaseValue>;
				DatabaseQueryResult = Array<DatabaseQueryResultRow>;				
				DatabaseQueryFailure := [query: DatabaseSqlQuery, boundParameters: DatabaseQueryBoundParameters, error: String];
			"
		);
	}

}
