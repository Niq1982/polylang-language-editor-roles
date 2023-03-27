# Polylang language editor roles for WordPress

Restricts users to edit posts, pages, terms etc. unless they have the editor role for that language translation. Requires a plugin that allows you set multiple roles for user, for example [Multiple Roles](https://wordpress.org/plugins/multiple-roles/).

## Filters

### Limit custom post translation capabilities

```php
add_filter('polylang_limit_post_language_caps', function($caps) {
    $caps[] = 'edit_my_custom_post';

    return $caps;
})
```
### Limit custom taxonomy term translation capabilities

```php
add_filter('polylang_limit_term_language_caps', function($caps) {
    $caps[] = 'edit_my_custom_term';

    return $caps;
})
```

### Allow access to all translations for a certain role
```php
add_filter('polylang_allow_all_language_roles', function($roles) {
    return ['administrator', 'super-admin'];
})
```
