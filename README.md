# WP Customize Builder
Builder module for WordPress which is based on Customizer API and WPKit.
### Dependencies
You need to use <a href="https://github.com/REDINKno/wpkit" target="_blank">WPKit</a>. 
### Examples
Render builder on frontend.
```
Builder::render( [
    'before' => '<div class="builder">',
    'after'  => '</div>'
] );
```