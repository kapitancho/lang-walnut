module $http/autowire/request-helper %% $http/autowire:

EmptyRequestBody := ();
EmptyRequestBody ==> HttpAutoWireRequestBodyToParameter ::
    ^{HttpRequest} => Map<Nothing, 0..0> :: [:];

JsonRequestBody := $[valueKey: String];
JsonRequestBody ==> HttpAutoWireRequestBodyToParameter ::
    ^request: {HttpRequest} => Result<Map<JsonValue>, InvalidJsonString> :: {
        request = request->shape(`HttpRequest);
        body = ?whenTypeOf(body= request.body) {
            `String: body,
            ~: ''
        };
        [:]->withKeyValue[key: $valueKey, value: body->jsonDecode!];
    };
