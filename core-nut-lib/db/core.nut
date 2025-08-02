module $db/core:

DatabaseSqlQuery = NonEmptyString;
DatabaseValue = String|Integer|Real|Boolean|Null;
DatabaseQueryDataRow = Map<DatabaseValue>;
DatabaseQueryBoundParameters = Array<DatabaseValue>|DatabaseQueryDataRow;
DatabaseQueryCommand = [query: DatabaseSqlQuery, boundParameters: DatabaseQueryBoundParameters];
DatabaseQueryResult = Array<DatabaseQueryDataRow>;
DatabaseQueryFailure := [query: DatabaseSqlQuery, boundParameters: DatabaseQueryBoundParameters, error: String];
DatabaseQueryFailure->error(=> String) :: $error;

DatabaseQueryFailure ==> ExternalError :: ExternalError[
    errorType: $->type->typeName,
    originalError: $,
    errorMessage: $error
];