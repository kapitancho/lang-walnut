module $http/middleware/cors %% $http/request-handler, $http/middleware:

CorsAllowedOrigins = Array<String, 1..>;
==> CorsAllowedOrigins :: ['*'];
CorsAllowedHeaders = Array<String>;
==> CorsAllowedHeaders :: ['Content-Type', 'Authorization', 'Location'];
CorsExposedHeaders = Array<String>;
==> CorsExposedHeaders :: ['Content-Type', 'Authorization', 'Location'];
CorsAllowedMethods = Array<HttpRequestMethod>;
==> CorsAllowedMethods :: `HttpRequestMethod[options, get, head, post, put, patch, delete]->values;

HttpCorsMiddleware := ();
HttpCorsMiddleware ==> HttpMiddleware %% [~CorsAllowedOrigins, ~CorsAllowedHeaders, ~CorsAllowedMethods, ~CorsExposedHeaders] :: {
    applyHeader = ^[headerName: String, values: Array<String>, response: {HttpResponse}] => {HttpResponse} :: {
        ?whenTypeOf(#values) {
            `Array<String, 1..>: #response->shape(`HttpResponse)->withHeader[headerName: #headerName, values: [#values->combineAsString(', ')]],
            ~: #response
        }
    };
    ^[request: {HttpRequest}, handler: {HttpRequestHandler}] => {HttpResponse} :: {
        response = ?whenValueOf(#request->shape(`HttpRequest).method) {
            HttpRequestMethod.options: [
                statusCode: 200,
                protocolVersion: HttpProtocolVersion.http_1_1,
                headers: [:],
                body: null
            ],
            ~: #handler->shape(`HttpRequestHandler)(#request)
        };
        response = applyHeader['Access-Control-Allow-Origin', %corsAllowedOrigins, response];
        response = applyHeader['Access-Control-Allow-Headers', %corsAllowedHeaders, response];
        response = applyHeader['Access-Control-Expose-Headers', %corsExposedHeaders, response];
        response = applyHeader['Access-Control-Allow-Methods', %corsAllowedMethods->map(^m: HttpRequestMethod => String :: m->asString), response];
        response
    }
};
