module $http/autowire/response-helper %% $http/autowire, $tpl:

NoResponseBody := $[statusCode: HttpResponseStatusCode];
NoResponseBody ==> HttpAutoWireResponseBodyFromParameter ::
    ^Any => HttpResponse %% [~HttpResponseBuilder] ::
        %httpResponseBuilder($statusCode);

RedirectResponseBody := $[statusCode: HttpResponseStatusCode];
RedirectResponseBody ==> HttpAutoWireResponseBodyFromParameter ::
    ^result: Any => Result<HttpResponse, Any> %% [~HttpResponseBuilder] :: {
        redirectValue = result=>as(`String);
        %httpResponseBuilder($statusCode)
            ->withHeader[headerName: 'Location', values: [redirectValue]]
    };

JsonResponseBody := $[statusCode: HttpResponseStatusCode];
JsonResponseBody ==> HttpAutoWireResponseBodyFromParameter ::
    ^result: Any => Result<HttpResponse, Any> %% [~HttpResponseBuilder] :: {
        jsonValue = result->asJsonValue;
        ?whenIsError(jsonValue) { jsonValue } ~ {
            {%httpResponseBuilder($statusCode)
                ->withHeader[headerName: 'Content-Type', values: ['application/json']]}
                ->withBody(jsonValue->stringify)
        }
    };

ContentResponseBody := $[statusCode: HttpResponseStatusCode, contentType: String];
ContentResponseBody ==> HttpAutoWireResponseBodyFromParameter ::
    ^result: Any => Result<HttpResponse, Any> %% [~HttpResponseBuilder] ::
        {%httpResponseBuilder($statusCode)
            ->withHeader[headerName: 'Content-Type', values: [$contentType]]}
            ->withBody(result => asString);