module $db/sql/quoter-mysql %% $db/sql/query-builder:

==> SqlQuoter :: {
	identifier = '`';
	escapedIdentifier = '``';
	value = '\`';
	valueChars = ['\\', '\`', '\n'];
	escapedValueChars = ['\\\\', '\\\`', '\\\n'];
    [
        quoteIdentifier: ^s: String => String :: [
            identifier, s->replace[match: '`', replacement: '``'], identifier
        ]->combineAsString(''),
        quoteValue: ^v: String|Integer|Real|Boolean|Null => String :: ?whenTypeOf(v) {
            `String: [
                value,
                v
                    ->replace[match: valueChars.0, replacement: escapedValueChars.0]
                    ->replace[match: valueChars.1, replacement: escapedValueChars.1]
                    ->replace[match: valueChars.2, replacement: escapedValueChars.2],
                value
            ]->combineAsString(''),
            `Integer|Real: v->asString,
            `Boolean: ?whenValueOf(v) { true: '1', false: '0' },
            `Null: 'NULL'
        }
    ]
};