module $http/autowire/view-response-helper %% $http/autowire:

ViewResponseBody := $[statusCode: HttpResponseStatusCode, contentType: String];
ViewResponseBody ==> HttpAutoWireResponseBodyFromParameter ::
    ^view: Any => Result<HttpResponse> %% [~HttpResponseBuilder, ~TemplateRenderer] :: {
        %httpResponseBuilder($statusCode)
            ->withHeader[headerName: 'Content-Type', values: [$contentType]]
            ->withBody(%templateRenderer->render(view)?)
    };
