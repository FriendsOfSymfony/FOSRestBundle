Full default configuration
==========================

```yaml
fos_rest:
    routing_loader:
        default_format: null
    view:
        default_engine: twig
        force_redirects:
            html: true
        formats:
            json: true
            xml: true
        templating_formats:
            html: true
        view_response_listener: 'force'
        failed_validation: HTTP_BAD_REQUEST
    exception:
        codes: ~
        messages: ~
    body_listener:
        decoders:
            json: fos_rest.decoder.json
            xml: fos_rest.decoder.xml
    format_listener:
        default_priorities: [html, '*/*']
        fallback_format: html
        prefer_extension: true
    service:
        router: router
        templating: templating
        serializer: serializer
        view_handler: fos_rest.view_handler.default
```

