## Documentation

### How to access the Swagger UI

This plugin add a page for Super Users only in Administration > Platform > Swagger

### Embed the swagger (optional)

You can embed swagger in your dev tools using the iframe :

```html
<!-- Auto resize iframe height (optional) -->
<script>
    function resizeIframe(obj) {
        setInterval(() => {
            obj.style.height = obj.contentWindow.document.documentElement.scrollHeight + 'px';
        }, 500);
    }
</script>

<!-- Swagger UI (required) -->
<iframe src="/index.php?module=Swagger&action=iframe" width="100%" onload="resizeIframe(this);"></iframe>
```


### Expose JSON OpenAPI file (optional)

The OpenAPI JSON configuration file is accessible via API using this URL : 

`/index.php?module=API&format=json&method=Swagger.getOpenApi`
