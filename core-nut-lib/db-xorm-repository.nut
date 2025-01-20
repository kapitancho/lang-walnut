module db-xorm-repository %% db-xorm:

OxRepository = $[~Ox, ~Type];
OxRepository[~Type, model: Type] @ ExternalError  :: [
    ox: {Ox[#model]} *> ('Failed to get orm model'),
    type: #type
];

EntryNotFound = $[key: DatabaseValue];
EntryNotFound->key(=> DatabaseValue) :: $key;

OxRepository->all(=> *Array) %% [~DatabaseConnector] :: {
    query = $ox->selectAllQuery /*'SELECT * FROM <table>'*/
        *> ('Failed to get query for orm model');
    {%databaseConnector
        -> query[query: query, boundParameters: []]}
        *> ('Failed to get entries from the database')
        -> map(^DatabaseQueryResultRow => Result<Any, HydrationError> :: #->hydrateAs($type))
        *> ('Failed to hydrate entries')
};

OxRepository->one(^DatabaseValue => *Result<Any, EntryNotFound>) %% [~DatabaseConnector] :: {
    query = $ox->selectOneQuery /*'SELECT * FROM <table> WHERE id = ?'*/
        *> ('Failed to get query for orm model');
    entries = {%databaseConnector
          -> query[query: query, boundParameters: [:]->withKeyValue[key: $ox->keyField, value: #]]
        } *> ('Failed to get entry from the database')
          ->map(^DatabaseQueryResultRow => Result<Any, HydrationError> :: #->hydrateAs($type))
          *> ('Failed to hydrate entries');
    ?whenTypeOf(entries) is {
        type{Array<1..>}: entries.0,
        ~: @EntryNotFound[key: #]
    }
};

OxRepository->insertOne(^Map<DatabaseValue> => *Null) %% [~DatabaseConnector] :: {
    query = $ox->insertQuery
        *> ('Failed to get query for orm model');
    result = {%databaseConnector->execute[query: query, boundParameters: #]}
        *> ('Failed to insert entry into the database');
    ?whenTypeOf(result) is {
        type{Integer<1..1>} : null,
        ~: @ExternalError[
            errorType: 'insert error',
            originalError: result,
            errorMessage: 'Failed to insert entry into the database'
        ]
    }
};

OxRepository->deleteOne(^DatabaseValue => *Result<Null, EntryNotFound>) %% [~DatabaseConnector] :: {
    query = $ox->deleteQuery
        *> ('Failed to get query for orm model');
    result = {%databaseConnector->execute[query: query, boundParameters: [:]->withKeyValue[key: $ox->keyField, value: #]]}
        -> errorAsExternal('Failed to delete entry from the database');
    ?whenTypeOf(result) is {
        type{Integer<1..1>} : null,
        ~: @EntryNotFound[key: #]
    }
};

OxRepository->updateOne(^Map<DatabaseValue> => *Result<Null, EntryNotFound>) %% [~DatabaseConnector] :: {
    entryId = #->item($ox->keyField) *> ('Failed to get entry key');
    query = $ox->updateQuery
        *> ('Failed to get query for orm model');
    result = {%databaseConnector->execute[query: query, boundParameters: #]}
        *> ('Failed to update entry in the database');
    ?whenTypeOf(result) is {
        type{Integer<1..1>} : null,
        ~: @EntryNotFound[key: entryId]
    }
};
