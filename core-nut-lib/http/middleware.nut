module $http/middleware %% $http/core:

CompositeHandler = #[defaultHandler: HttpRequestHandler, middlewares: Array<HttpMiddleware>];

CompositeHandler ==> HttpRequestHandler :: {
    ^[request: HttpRequest] => Result<HttpResponse, Any> :: {
        ?whenTypeOf($middlewares) is {
            type{Array<HttpMiddleware, 1..>}: {
                m = $middlewares => withoutFirst;
                {m.element} [#request, {CompositeHandler[$defaultHandler, m.array]}=>as(type{HttpRequestHandler})]
            },
            ~: $defaultHandler[#request]
        }
    }
};

CorsAllowedOrigins = Array<String, 1..>;
==> CorsAllowedOrigins :: ['*'];
CorsAllowedHeaders = Array<String>;
==> CorsAllowedHeaders :: ['Content-Type', 'Authorization', 'Location', 'X-Token'];
CorsExposedHeaders = Array<String>;
==> CorsExposedHeaders :: ['Content-Type', 'Authorization', 'Location', 'X-Token'];
CorsAllowedMethods = Array<String>;
==> CorsAllowedMethods :: ['OPTIONS', 'GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE'];

CorsMiddleware = [];
CorsMiddleware ==> HttpMiddleware %% [~CorsAllowedOrigins, ~CorsAllowedHeaders, ~CorsAllowedMethods, ~CorsExposedHeaders] :: {
    applyHeader = ^[headerName: String, values: Array<String>, response: HttpResponse] => HttpResponse :: {
        values = #values;
        ?whenTypeOf(values) is {
            type{Array<String, 1..>}: #response->withHeader[headerName: #headerName, values: [values->combineAsString(', ')]],
            ~: #response
        }
    };
    ^[request: HttpRequest, handler: HttpRequestHandler] => Result<HttpResponse, Any> :: {
        response = ?whenValueOf(#request.method) is {
            HttpRequestMethod.OPTIONS: [
                statusCode: 200,
                protocolVersion: HttpProtocolVersion.HTTP11,
                headers: [:],
                body: null
            ],
            ~: #handler[#request]
        };
        ?whenTypeOf(response) is {
            type{HttpResponse}: {
                response = applyHeader['Access-Control-Allow-Origin', %corsAllowedOrigins, response];
                response = applyHeader['Access-Control-Allow-Headers', %corsAllowedHeaders, response];
                response = applyHeader['Access-Control-Expose-Headers', %corsExposedHeaders, response];
                response = applyHeader['Access-Control-Allow-Methods', %corsAllowedMethods, response];
                response
            },
            ~: response
        }
    }
};

NotFoundHandler = [];
NotFoundHandler ==> HttpRequestHandler %% CreateHttpResponse :: {
    ^[request: HttpRequest] => Result<HttpResponse, Any> :: %(404)
};

UncaughtExceptionHandler = :[];
UncaughtExceptionHandler ==> HttpMiddleware %% CreateHttpResponse :: {
    ^[request: HttpRequest, handler: HttpRequestHandler] => HttpResponse :: {
        result = #handler[#request];
        ?whenTypeOf(result) is {
            type{HttpResponse}: result,
            ~: %(500)
        }
    }
};

LookupRouterMapping = Array<[path: String, type: Type]>;
LookupRouter = $[routerMapping: LookupRouterMapping];
LookupRouter ==> HttpMiddleware %% DependencyContainer :: {
    run = ^[request: HttpRequest, type: Type] => Result<HttpResponse, Any> :: {
        handler = %->valueOf(#type);
        rh = handler=>as(type{HttpRequestHandler});
        rh[#request]
    };

    ^[request: HttpRequest, handler: HttpRequestHandler] => Result<HttpResponse, Any> :: {
        request = #request;
        withUpdatedRequestPath = ^[path: String] => HttpRequest :: {[
            protocolVersion: request.protocolVersion,
            headers: request.headers,
            body: request.body,
            requestTarget: request.requestTarget->substringRange[start: #path->length, end: 9999],
            method: request.method
        ]}->as(type{HttpRequest});

        kv = $routerMapping->findFirst(
            ^[path: String, type: Type] => Boolean :: {
                request.requestTarget->startsWith(#path)
            }
        );
        ?whenTypeOf(kv) is {
            type{[path: String, type: Type]}: run[withUpdatedRequestPath[kv.path], kv.type],
            ~: #handler[request]
        }
    }
};
