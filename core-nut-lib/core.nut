module core:

/* constructor support */
Global = :[];

/* constructor support */
Constructor = :[];

/* number and range related atoms */
NotANumber = :[];
MinusInfinity = :[];
PlusInfinity = :[];
InvalidRange = :[];
IntegerRange <: [minValue: Integer|MinusInfinity, maxValue: Integer|PlusInfinity] @ InvalidRange ::
    ?whenTypeOf(#) is { type[minValue: Integer, maxValue: Integer]:
        ?when (#.minValue > #.maxValue) { => @InvalidRange[] }};
RealRange <: [minValue: Real|MinusInfinity, maxValue: Real|PlusInfinity] @ InvalidRange ::
    ?whenTypeOf(#) is { type[minValue: Real, maxValue: Real]:
        ?when (#.minValue > #.maxValue) { => @InvalidRange[] }};
LengthRange <: [minLength: Integer<0..>, maxLength: Integer<0..>|PlusInfinity] @ InvalidRange ::
    ?whenTypeOf(#) is { type[minLength: Integer<0..>, maxLength: Integer<0..>]:
        ?when (#.minLength > #.maxLength) { => @InvalidRange[] }};

/* dependency container */
DependencyContainer = :[];
DependencyContainerErrorType = :[CircularDependency, Ambiguous, NotFound, UnsupportedType, ErrorWhileCreatingValue];
DependencyContainerError = $[targetType: Type, errorOnType: Type, errorMessage: String, errorType: DependencyContainerErrorType];
DependencyContainerError->errorMessage(=> String) :: $errorMessage;
DependencyContainerError->targetType(=> Type) :: $targetType;
DependencyContainerError->errorType(=> DependencyContainerErrorType) :: $errorType;

/* json value */
JsonValue = Null|Boolean|Integer|Real|String|Array<`JsonValue>|Map<`JsonValue>/*|Result<Nothing, `JsonValue>*/|Mutable<`JsonValue>;
InvalidJsonString = $[value: String];
InvalidJsonString->value(=> String) :: $value;
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
HydrationError->errorMessage(=> String) :: ''->concatList[
    'Error in ', $hydrationPath, ': ', $errorMessage
];

/* IO etc. */
ExternalError = $[errorType: String, originalError: Any, errorMessage: String];

/* Random generator */
Random = :[];

/* Password handling */
PasswordString = $[value: String];

