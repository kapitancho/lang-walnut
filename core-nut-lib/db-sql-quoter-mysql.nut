module db-sql-quoter-mysql %% db-sql-query-builder:

==> SqlQuoter :: {
	identifier = '`';
	escapedIdentifier = '``';
	value = '\`';
	valueChars = ['\\', '\`', '\n'];
	escapedValueChars = ['\\\\', '\\\`', '\\\n'];
    [
        quoteIdentifier: ^String => String :: [
            identifier, # /*->replace...*/, identifier
        ]->combineAsString(''),
        quoteValue: ^String|Integer|Real|Boolean|Null => String :: ?whenTypeOf(#) is {
            type{String}: [
                value, # /*->replace...*/, value
            ]->combineAsString(''),
            type{Integer|Real}: #->asString,
            type{Boolean}: ?whenValueOf(#) is { true: '1', false: '0' },
            type{Null}: 'NULL'
        }
    ]
};