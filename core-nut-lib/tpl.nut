module tpl:

UnableToRenderTemplate = $[type: Type];
UnableToRenderTemplate ==> String :: 'Unable to render template of type '->concat($type->printed);

Template <: Mutable<String>;

TemplateRenderer = :[];
TemplateRenderer->render(^view: Any => Result<String, UnableToRenderTemplate>) :: {
    tpl = view->as(type{Template});
    ?whenTypeOf(tpl) is {
        type{Template}: tpl->value,
        ~: @UnableToRenderTemplate[view->type]
    }
};

