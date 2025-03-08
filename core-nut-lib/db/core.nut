module $db/core:

DatabaseConnection = #[dsn: String];
DatabaseConnector = $[connection: DatabaseConnection];

DatabaseSqlQuery = String<1..>;
DatabaseValue = String|Integer|Real|Boolean|Null;
DatabaseQueryBoundParameters = Array<DatabaseValue>|Map<DatabaseValue>;
DatabaseQueryCommand = [query: DatabaseSqlQuery, boundParameters: DatabaseQueryBoundParameters];
DatabaseQueryResultRow = Map<DatabaseValue>;
DatabaseQueryResult = Array<DatabaseQueryResultRow>;
DatabaseQueryFailure = #[query: DatabaseSqlQuery, boundParameters: DatabaseQueryBoundParameters, error: String];
DatabaseQueryFailure->error(=> String) :: $error;

DatabaseQueryFailure ==> ExternalError :: ExternalError[
    errorType: $->type->typeName,
    originalError: $,
    errorMessage: $error
];

==> DatabaseConnector %% DatabaseConnection :: DatabaseConnector[connection: %];
