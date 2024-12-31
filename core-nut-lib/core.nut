module core:

/* constructor support */
Constructor = :[];

/* number and range related atoms */
NotANumber = :[];
MinusInfinity = :[];
PlusInfinity = :[];
InvalidRange = :[];
IntegerRange <: [minValue: Integer|MinusInfinity, maxValue: Integer|PlusInfinity] @ InvalidRange ::
    ?whenTypeOf(#) is { type[minValue: Integer, maxValue: Integer]:
        ?whenIsTrue { #.minValue > #.maxValue: => @InvalidRange[] }};
RealRange <: [minValue: Real|MinusInfinity, maxValue: Real|PlusInfinity] @ InvalidRange ::
    ?whenTypeOf(#) is { type[minValue: Real, maxValue: Real]:
        ?whenIsTrue { #.minValue > #.maxValue: => @InvalidRange[] }};
LengthRange <: [minLength: Integer<0..>, maxLength: Integer<0..>|PlusInfinity] @ InvalidRange ::
    ?whenTypeOf(#) is { type[minLength: Integer<0..>, maxLength: Integer<0..>]:
        ?whenIsTrue { #.minLength > #.maxLength: => @InvalidRange[] }};

/* dependency container */
DependencyContainer = :[];
DependencyContainerError = $[targetType: Type, errorOnType: Type, errorMessage: String];
DependencyContainerError->errorMessage(^Null => String) :: $errorMessage;
DependencyContainerError->targetType(^Null => Type) :: $targetType;

/* json value */
JsonValue = Null|Boolean|Integer|Real|String|Array<`JsonValue>|Map<`JsonValue>/*|Result<Nothing, `JsonValue>*/|Mutable<`JsonValue>;
InvalidJsonString = $[value: String];
InvalidJsonString->value(^Null => String) :: $value;
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
    'Error in ', $hydrationPath, ': ', $errorMessage
];

/* IO etc. */
ExternalError = $[errorType: String, originalError: Any, errorMessage: String];

/* Random generator */
Random = :[];

/* Password handling */
PasswordString = $[value: String];

