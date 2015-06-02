Full default configuration
==========================

.. code-block:: yaml

    # Default configuration for extension with alias: "fos_rest"

    fos_rest:
        disable_csrf_role:    null
        access_denied_listener:
            enabled:              false
            service:              null
            formats:

                # Prototype
                name:                 ~
        unauthorized_challenge:  null
        param_fetcher_listener:
            enabled:              false
            force:                false
            service:              null
        cache_dir:            '%kernel.cache_dir%/fos_rest'
        allowed_methods_listener:
            enabled:              false
            service:              null
        routing_loader:
            default_format:       null
            include_format:       true
        body_converter:
            enabled:              false
            validate:             false
            validation_errors_argument:  validationErrors
        service:
            router:               router
            templating:           templating
            serializer:           null
            view_handler:         fos_rest.view_handler.default
            exception_handler:    fos_rest.view.exception_wrapper_handler
            inflector:            fos_rest.inflector.doctrine
            validator:            validator
        serializer:
            version:              null
            groups:               []
            serialize_null:       false
        view:
            default_engine:       twig
            force_redirects:

                # Prototype
                name:                 ~
            mime_types:
                enabled:              false
                service:              null
                formats:

                    # Prototype
                    name:                 ~
            formats:

                # Prototype
                name:                 ~
            templating_formats:

                # Prototype
                name:                 ~
            view_response_listener:
                enabled:              false
                force:                false
                service:              null
            failed_validation:    400
            empty_content:        204
            exception_wrapper_handler:  null
            serialize_null:       false
            jsonp_handler:
                callback_param:       callback
                callback_filter:      '/(^[a-z0-9_]+$)|(^YUI\.Env\.JSONP\._[0-9]+$)/i'
                mime_type:            application/javascript+jsonp
        exception:
            enabled:              false
            exception_controller:  null
            codes:

                # Prototype
                name:                 ~
            messages:

                # Prototype
                name:                 ~
        body_listener:
            enabled:              true
            service:              null
            default_format:       null
            throw_exception_on_unsupported_content_type:  false
            decoders:

                # Prototype
                name:                 ~
            array_normalizer:
                service:              null
                forms:                false
        format_listener:
            enabled:              false
            service:              null
            rules:

                # URL path info
                path:                 null

                # URL host name
                host:                 null

                # Method for URL
                methods:              null
                stop:                 false
                prefer_extension:     true
                fallback_format:      html
                exception_fallback_format:  null
                priorities:           []
            media_type:
                enabled:              false
                service:              null
                version_regex:        '/(v|version)=(?P<version>[0-9\.]+)/'
