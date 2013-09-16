Full default configuration
==========================

```yaml
fos_rest:
    access_denied_listener:

        # Prototype
        name:                 []
    param_fetcher_listener:  false
    cache_dir:            %kernel.cache_dir%/fos_rest
    allowed_methods_listener:  false
    routing_loader:
        default_format:       ~
        include_format:       true
    body_converter:
        enabled:              false
        validate:             false
        validation_errors_argument:  validationErrors
    service:
        router:               router
        templating:           templating
        serializer:           jms_serializer.serializer
        view_handler:         fos_rest.view_handler.default
        inflector:            fos_rest.inflector.doctrine
        validator:            validator
    serializer:
        version:              ~
        groups:               []
        serialize_null:       false
    view:
        default_engine:       twig
        force_redirects:

            # Prototype
            name:                 []
        mime_types:

            # Prototype
            name:                 []
        formats:

            # Prototype
            name:                 []
        templating_formats:

            # Prototype
            name:                 []
        view_response_listener:  false
        failed_validation:    400
        empty_content:        204
        exception_wrapper_handler:  FOS\RestBundle\View\ExceptionWrapperHandler
        serialize_null:       false
        jsonp_handler:
            callback_param:       callback
            callback_filter:      /(^[a-z0-9_]+$)|(^YUI\.Env\.JSONP\._[0-9]+$)/i
            mime_type:            application/javascript+jsonp
    exception:
        codes:

            # Prototype
            name:                 []
        messages:

            # Prototype
            name:                 []
    body_listener:
        decoders:

            # Prototype
            name:                 []
    format_listener:
        rules:

            # URL path info
            path:                 ~

            # URL host name
            host:                 ~
            prefer_extension:     true
            fallback_format:      html
            priorities:

                # Prototype
                name:                 []
```

