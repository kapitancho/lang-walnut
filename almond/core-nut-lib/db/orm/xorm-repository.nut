module $db/orm/xorm-repository %% $db/orm/xorm, $db/connection:

OxRepository := $[~Ox, ~Type];
OxRepository[~Type, model: Type<Any>] @@ ExternalError  :: [
    ox: {Ox[#model]} @* ('Failed to get orm model')!,
    type: #type
];

EntryNotFound := [key: DatabaseValue];
DuplicateEntry := [key: DatabaseValue];

==> DatabaseConnection :: DatabaseConnection![dsn: ''];

OxRepository->all(=> Array*) %% ~DatabaseConnector :: {
    databaseConnector
        -> query[query: $ox->selectAllQuery, boundParameters: []]
        @* ('Failed to get entries from the database')
        -> map(^row: DatabaseQueryDataRow => Result<Any, HydrationError> :: row->hydrateAs($type))
        @* ('Failed to hydrate entries')
};

OxRepository->one(^v: DatabaseValue => Result<Any, EntryNotFound>*) %% ~DatabaseConnector :: {
    entries = databaseConnector
        -> query[query: $ox->selectOneQuery, boundParameters: [:]->withKeyValue[key: $ox->keyField, value: #]]
            @* ('Failed to get entry from the database')
        ->map(^row: DatabaseQueryDataRow => Result<Any, HydrationError> :: row->hydrateAs($type))
            @* ('Failed to hydrate entries')!;
    ?whenTypeOf(entries) {
        `Array<1..>: entries.0,
        ~: @EntryNotFound![key: v]
    }
};

OxRepository->insertOne(^v: {DatabaseQueryDataRow} => Result<Null, DuplicateEntry>*) %% ~DatabaseConnector :: {
    v = v->shape(`DatabaseQueryDataRow);
    entryId = v->item($ox->keyField) ?* ('Failed to get entry key')!;
    result = 
        databaseConnector->execute[query: $ox->insertQuery, boundParameters: v]
            @* ('Failed to insert entry into the database');
    ?whenTypeOf(result) {
        `Error<DatabaseQueryFailure> : ?whenIsTrue {
            result->error.error->contains('SQLSTATE[23'): @DuplicateEntry![key: entryId],
            ~: result @* ('Failed to insert entry into the database')
        },
        `Error: result @* ('Failed to insert entry into the database'),
        `Integer<1..1> : null,
        ~: @ExternalError[
            errorType: 'xorm-insert',
            originalError: null,
            errorMessage: 'Failed to insert entry into the database'
        ]
    }
};

OxRepository->deleteOne(^v: DatabaseValue => Result<Null, EntryNotFound>*) %% ~DatabaseConnector :: {
    result = 
        databaseConnector->execute[query: $ox->deleteQuery, boundParameters: [:]->withKeyValue[key: $ox->keyField, value: v]]
            @* ('Failed to delete entry from the database')!;
    ?whenTypeOf(result) {
        `Integer<1..1> : null,
        ~: @EntryNotFound![key: v]
    }
};

OxRepository->updateOne(^v: {DatabaseQueryDataRow} => Result<Null, EntryNotFound>*) %% [~DatabaseConnector] :: {
    v = v->shape(`DatabaseQueryDataRow);
    entryId = v->item($ox->keyField) ?* ('Failed to get entry key')!;
    result = 
        %databaseConnector->execute[query: $ox->updateQuery, boundParameters: v]
            @* ('Failed to update entry in the database');
    ?whenTypeOf(result) {
        `Integer<1..1> : null,
        ~: @EntryNotFound![key: entryId]
    }
};
