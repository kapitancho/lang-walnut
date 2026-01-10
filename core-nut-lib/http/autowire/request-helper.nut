module $http/autowire/request-helper %% $http/autowire:

EmptyRequestBody := ();
EmptyRequestBody ==> HttpAutoWireRequestBodyToParameter ::
    ^{HttpRequest} => Map<Nothing, 0..0> :: [:];

JsonRequestBody := $[valueKey: String];
JsonRequestBody ==> HttpAutoWireRequestBodyToParameter ::
    ^request: {HttpRequest} => Result<Map<JsonValue>, InvalidJsonString> :: {
        request = request->shape(`HttpRequest);
        body = request.body;
        body = ?whenTypeOf(body) {
            `String: body,
            ~: ''
        };
        value = body->jsonDecode;
        ?whenTypeOf(value) {
            `Error<InvalidJsonString>: value,
            `JsonValue: [:]->withKeyValue[key: $valueKey, value: value]
        }
    };
