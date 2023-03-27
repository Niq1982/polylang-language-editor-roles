<?php
/*
Plugin Name: Polylang language editor roles
Plugin URI: https://niq.kapsi.fi
Description: Users cant edit posts or pages unless they have the editor role for that language
Author: Niku Hietanen
Version: 1.0.0
Author URI: https://niq.kapsi.fi
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

    $edit_capabilities_to_limit = [
        'edit_post',
        'edit_page',
        'edit_term',
    ];

    if (!in_array($cap, $edit_capabilities_to_limit)) {
        return $caps;
    }

    $user = get_userdata($user_id);

    if (in_array('administrator', $user->roles)) {
        return $caps;
    }

    if ($cap === 'edit_term') {
        $term_id = isset($args[0]) ? $args[0] : 0;
        $lang = pll_get_term_language($term_id);
    } else {
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