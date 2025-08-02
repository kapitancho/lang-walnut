module $db/orm/xorm-repository %% $db/orm/xorm, $db/connection:

OxRepository := $[~Ox, ~Type];
OxRepository[~Type, model: Type] @ ExternalError  :: [
    ox: {Ox[#model]} *> ('Failed to get orm model'),
    type: #type
];

EntryNotFound := $[key: DatabaseValue];
EntryNotFound->key(=> DatabaseValue) :: $key;

DuplicateEntry := $[key: DatabaseValue];
DuplicateEntry->key(=> DatabaseValue) :: $key;

OxRepository->all(=> *Array) %% [~DatabaseConnector] :: {
    query = $ox->selectAllQuery /*'SELECT * FROM <table>'*/
        *> ('Failed to get query for orm model');
    {%databaseConnector
        -> query[query: query, boundParameters: []]}
        *> ('Failed to get entries from the database')
        -> map(^DatabaseQueryDataRow => Result<Any, HydrationError> :: #->hydrateAs($type))
        *> ('Failed to hydrate entries')
};

OxRepository->one(^v: DatabaseValue => *Result<Any, EntryNotFound>) %% [~DatabaseConnector] :: {
    query = $ox->selectOneQuery /*'SELECT * FROM <table> WHERE id = ?'*/
        *> ('Failed to get query for orm model');
    entries = {%databaseConnector
          -> query[query: query, boundParameters: [:]->withKeyValue[key: $ox->keyField, value: #]]
        } *> ('Failed to get entry from the database')
          ->map(^DatabaseQueryDataRow => Result<Any, HydrationError> :: #->hydrateAs($type))
          *> ('Failed to hydrate entries');
    ?whenTypeOf(entries) is {
        `Array<1..>: entries.0,
        ~: @EntryNotFound[key: v]
    }
};

OxRepository->insertOne(^v: {DatabaseQueryDataRow} => *Result<Null, DuplicateEntry>) %% [~DatabaseConnector] :: {
    v = v->shape(`DatabaseQueryDataRow);
    entryId = v->item($ox->keyField) *> ('Failed to get entry key');
    query = $ox->insertQuery
        *> ('Failed to get query for orm model');
    result = {%databaseConnector->execute[query: query, boundParameters: v]}
        /* *> ('Failed to insert entry into the database') */;
    ?whenTypeOf(result) is {
        `Error<DatabaseQueryFailure> : ?whenIsTrue {
            result->error.error->contains('SQLSTATE[23'): @DuplicateEntry[key: entryId],
            ~: result *> ('Failed to insert entry into the database')
        },
        `Error: result *> ('Failed to insert entry into the database'),
        `Integer<1..1> : null,
        ~: @ExternalError[
            errorType: 'xorm-insert',
            originalError: null,
            errorMessage: 'Failed to insert entry into the database'
        ]
    }
};

OxRepository->deleteOne(^v: DatabaseValue => *Result<Null, EntryNotFound>) %% [~DatabaseConnector] :: {
    query = $ox->deleteQuery
        *> ('Failed to get query for orm model');
    result = {%databaseConnector->execute[query: query, boundParameters: [:]->withKeyValue[key: $ox->keyField, value: v]]}
        -> errorAsExternal('Failed to delete entry from the database');
    ?whenTypeOf(result) is {
        `Integer<1..1> : null,
        ~: @EntryNotFound[key: v]
    }
};

OxRepository->updateOne(^v: {DatabaseQueryDataRow} => *Result<Null, EntryNotFound>) %% [~DatabaseConnector] :: {
    v = v->shape(`DatabaseQueryDataRow);
    entryId = v->item($ox->keyField) *> ('Failed to get entry key');
    query = $ox->updateQuery
        *> ('Failed to get query for orm model');
    result = {%databaseConnector->execute[query: query, boundParameters: v]}
        *> ('Failed to update entry in the database');
    ?whenTypeOf(result) is {
        `Integer<1..1> : null,
        ~: @EntryNotFound[key: entryId]
    }
};
