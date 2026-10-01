<?php
/**
 * Plugin Name: Spam Protected Email
 * Plugin URI:  https://github.com/w0yl/spam-protected-email-wordpress-plugin
 * Description: Inline block editor tool to protect email links using Spencer Mortensen's 1.5 Display None and 2.8 Conversion JS methods showcased at https://spencermortensen.com/articles/email-obfuscation/.
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:      SCARC
 * License:     GPL v2 or later
 */

if (!defined('ABSPATH')) {
    exit;
}

// 1. Enqueue Editor Assets
function spe_enqueue_editor_assets() {
    wp_enqueue_script(
        'spe-editor-js',
        plugins_url('build/index.js', __FILE__),
        array('wp-rich-text', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n'),
        '1.0.0',
        true
    );
}
add_action('enqueue_block_editor_assets', 'spe_enqueue_editor_assets');

// 2. Enqueue Front-End Assets
function spe_enqueue_frontend_assets() {
    wp_enqueue_style(
        'spe-frontend-css',
        plugins_url('spam-protected-email.css', __FILE__),
        array(),
        '1.0.0'
    );
}
add_action('wp_enqueue_scripts', 'spe_enqueue_frontend_assets');

// 3. Front-End Filter: Outputs Obfuscated HTML & Browser-Side JS De-obfuscator
function spe_filter_protected_emails($content) {
    if (is_admin() || empty($content) || strpos($content, 'data-spe-email') === false) {
        return $content;
    }

    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    
    // Wrap in container to ensure DOMDocument parses fragments cleanly
    $dom->loadHTML(
        mb_convert_encoding('<div>' . $content . '</div>', 'HTML-ENTITIES', 'UTF-8'), 
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );
    libxml_clear_errors();

    $xpath = new DOMXPath($dom);
    $nodes = $xpath->query('//a[@data-spe-email]');

    if (!$nodes || $nodes->length === 0) {
        return $content;
    }

    foreach ($nodes as $node) {
        $raw_email = trim($node->getAttribute('data-spe-email'));
        $visible_text = trim($node->nodeValue);

        if (empty($raw_email)) {
            continue;
        }

        if (strpos($raw_email, '@') === false) {
            $user =$raw_email;
            $at = '';
            $domain = '';
        } else {
            $email_parts = explode('@', $raw_email, 2);
            $user = $email_parts[0];
            $at = '(()7';
            $domain = $email_parts[1];
        }


        // Format obfuscated href string for client-side JS reconstruction
        $first_char = mb_substr($user, 0, 1);
        $rest_user = mb_substr($user, 1);

        $domain_parts = explode('.', $domain);
        if (count($domain_parts) === 1) {
            $tld = $domain;
            $domain_body = '';
        } else {
            $tld = '.' . array_pop($domain_parts);
            $domain_body = implode(')(3', $domain_parts);
        }


        // Raw HTML href attribute output
        $obfuscated_href = 'to)(' . $rest_user . $at . $domain_body . '/)(';
        $unique_id = 'spe-' . wp_generate_password(8, false);

        $node->removeAttribute('data-spe-email');
        $node->setAttribute('class', 'spe-email-link');
        $node->setAttribute('id', $unique_id);
        $node->setAttribute('href', $obfuscated_href);

        // Method 1.5: If visible text contains @, inject hidden decoy element
        if (strpos($visible_text, '@') !== false) {
            $vis_parts = explode('@', $visible_text, 2);
            $decoy = wp_generate_password(4, false);

            while ($node->hasChildNodes()) {
                $node->removeChild($node->firstChild);
            }

            $node->appendChild($dom->createTextNode($vis_parts[0]));
            
            $span = $dom->createElement('span', '.' . $decoy);
            $span->setAttribute('class', 'spe-hide');
            $node->appendChild($span);

            $node->appendChild($dom->createTextNode('@' . $vis_parts[1]));
        }

        // Method 2.8: Browser-side JS de-obfuscation script
        $script = $dom->createElement('script');
        $script->nodeValue = sprintf(
            "'use strict'; document.addEventListener('DOMContentLoaded', function () { var a = document.getElementById('%s'); if (!a) return; a.setAttribute('href', a.getAttribute('href').replace('(()7','@').replace(/\)\(3/g, '.').replace('/)(', '%s').replace('to)(', 'mailto:%s')); });",
            esc_js($unique_id),
            esc_js($tld),
            esc_js($first_char)
        );

        if ($node->nextSibling) {
            $node->parentNode->insertBefore($script, $node->nextSibling);
        } else {
            $node->parentNode->appendChild($script);
        }
    }

    // Extract the inner HTML of our wrapper <div> tag
    $wrapper = $dom->getElementsByTagName('div')->item(0);
    $output = '';
    if ($wrapper) {
        foreach ($wrapper->childNodes as $child) {
            $output .= $dom->saveHTML($child);
        }
    }

    return !empty($output) ? $output : $content;
}
add_filter('the_content', 'spe_filter_protected_emails');