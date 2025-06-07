module $db/sql/quoter-mysql %% $db/sql/query-builder:

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
            `String: [
                value, # /*->replace...*/, value
            ]->combineAsString(''),
            `Integer|Real: #->asString,
            `Boolean: ?whenValueOf(#) is { true: '1', false: '0' },
            `Null: 'NULL'
        }
    ]
};