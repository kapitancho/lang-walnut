module core:

/* constructor support */
Constructor = :[];

/* number and range related atoms */
NotANumber = :[];
MinusInfinity = :[];
PlusInfinity = :[];

/* dependency container */
DependencyContainer = :[];
DependencyContainerError = $[targetType: Type, errorOnType: Type, errorMessage: String];
DependencyContainerError->errorMessage(^Null => String) :: $.errorMessage;
DependencyContainerError->targetType(^Null => Type) :: $.targetType;

/* json value */
JsonValue = Null|Boolean|Integer|Real|String|Array<`JsonValue>|Map<`JsonValue>/*|Result<Nothing, `JsonValue>*/|Mutable<`JsonValue>;
InvalidJsonString = $[value: String];
InvalidJsonString->value(^Null => String) :: $.value;
InvalidJsonValue = $[value: Any];

/* arrays and maps */
IndexOutOfRange = $[index: Integer];
MapItemNotFound = $[key: String];
ItemNotFound = :[];
SubstringNotInString = :[];

/* casts */
CastNotAvailable = $[from: Type, to: Type];

/* enumerations */
UnknownEnumerationValue = $[enumeration: Type, value: String];

/* hydration */
HydrationError = $[value: Any, hydrationPath: String, errorMessage: String];
HydrationError->errorMessage(^Null => String) :: ''->concatList[
    'Error in ', $.hydrationPath, ': ', $.errorMessage
];

/* IO etc. */
ExternalError = $[errorType: String, originalError: Any, errorMessage: String];

/* Random generator */
Random = :[];

/* Password handling */
PasswordString = $[value: String];

