module $db/core:

DatabaseSqlQuery = NonEmptyString;
DatabaseValue = String|Integer|Real|Boolean|Null;
DatabaseQueryBoundParameters = Array<DatabaseValue>|Map<DatabaseValue>;
DatabaseQueryCommand = [query: DatabaseSqlQuery, boundParameters: DatabaseQueryBoundParameters];
DatabaseQueryResultRow = Map<DatabaseValue>;
DatabaseQueryResult = Array<DatabaseQueryResultRow>;
DatabaseQueryFailure := [query: DatabaseSqlQuery, boundParameters: DatabaseQueryBoundParameters, error: String];
DatabaseQueryFailure->error(=> String) :: $error;

DatabaseQueryFailure ==> ExternalError :: ExternalError[
    errorType: $->type->typeName,
    originalError: $,
    errorMessage: $error
];