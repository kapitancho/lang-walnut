module $core:

/* constructor support */
Constructor := ();

DependencyContainer := ();

/* arrays and maps */
IndexOutOfRange := [index: Integer];
MapItemNotFound := [key: String];
ItemNotFound := ();
SubstringNotInString := ();
SliceNotInBytes := ();


/* CLI */
CliEntryPoint = ^Array<String> => String;

ExternalError := $[errorType: String, originalError: Any, errorMessage: String];

JsonValue = Null|Boolean|String|Array<\JsonValue>|Map<String:\JsonValue>|Set<\JsonValue>|Mutable<\JsonValue>;