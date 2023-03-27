<?php
/*
Plugin Name: Polylang language editor roles
Plugin URI: https://github.com/Niq1982/polylang-language-editor-roles
Description: Restricts users to edit posts, pages, terms etc.unless they have the editor role for that language. Requires a plugin that allows you set multiple roles for user, for example "Multiple Roles".
Author: Niku Hietanen
Version: 1.0.0
Author URI: https://github.com/Niq1982/
*/

namespace PolylangEditorRoles;

add_filter(
    'map_meta_cap', __NAMESPACE__ . '\restrict_editing_for_english_editor_role',
    99,
    4
);

add_action(
    'init',
    __NAMESPACE__ . '\add_language_roles'
);

function restrict_editing_for_english_editor_role($caps, $cap, $user_id, $args)
{
    if (!function_exists('pll_get_post_language')) {
        return $caps;
    }

    $post_capabilities_to_limit = apply_filters(
        'polylang_limit_post_language_caps',
        [
            'edit_post',
            'edit_page',
        ]
    );

    $term_capabilities_to_limit = apply_filters(
        'polylang_limit_term_language_caps',
        [
            'edit_term',
        ]
    );

    if (!in_array($cap, array_merge($post_capabilities_to_limit, $term_capabilities_to_limit))) {
        return $caps;
    }

    $user = get_userdata($user_id);

    if (in_array('administrator', $user->roles)) {
        return $caps;
    }

    if (in_array($cap, $term_capabilities_to_limit)) {
        $term_id = isset($args[0]) ? $args[0] : 0;
        $lang = pll_get_term_language($term_id);
    } elseif (in_array($cap, $post_capabilities_to_limit)) {
        $post_id = isset($args[0]) ? $args[0] : 0;
        $lang = pll_get_post_language($post_id);
    }

    if (!in_array("{$lang}-editor", $user->roles)) {
        $caps = ['do_not_allow'];
    }

    return $caps;
}

/**
 * Add language roles for each languages. Save the state to
 * transient so this code wont run all the time
 */
function add_language_roles()
{
    if (!function_exists('pll_get_post_language')) {
        return;
    }
    if (intval(get_transient('polylang_language_roles_set')) === 1) {
        return;
    }
    $languages = pll_languages_list(['fields' => 'slug']);

    foreach ($languages as $language) {
        add_role(
            "{$language}-editor",
            strtoupper($language) . ' editor',
        );
    }

    set_transient('polylang_language_roles_set', 1, PHP_INT_MAX);
}