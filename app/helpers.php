<?php

use Carbon\Carbon;

/**
 * Return html class according to submission status.
 * 
 * @return string
 */
if (!function_exists('submission_status_color')) {
    function submission_status_color($status)
    {
        switch ($status) {
            case 'accepted':
                return 'badge-success';
                break;
            case 'rejected':
                return 'badge-danger';
                break;
            case 'pending':
                return 'badge-warning';
                break;
            default:
                return '';
                break;
        }
    }
}

/**
 * Return user avatar url if not null.
 * 
 * @return string
 */
if (!function_exists('get_avatar_url')) {
    function get_avatar_url($user)
    {
        if ($user->photo_path != null) {
            return env('APP_URL') . $user->photo_path;
        } else {
            return asset('assets/img/avatar-default.png');
        }
    }
}

/**
 * Return submission cover url if not null
 * 
 * @return string
 */
if (!function_exists('get_cover_url')) {
    function get_cover_url($submission)
    {
        if ($submission->cover_path != null) {
            return env('APP_URL') . $submission->cover_path;
        } else {
            return asset('assets/img/default-placeholder.png');
        }
    }
}

/**
 * Return submission audio url if not null.
 * 
 * @return string
 */
if (!function_exists('get_audio_url')) {
    function get_audio_url($submission)
    {
        if ($submission->audio_path != null) {
            return env('APP_URL') . $submission->audio_path;
        } else {
            return null;
        }
    }
}

/**
 * Return formatted category tembang.
 * 
 * @return string
 */
if (!function_exists('format_tembang_category')) {
    function format_tembang_category($submission)
    {
        $result = $submission->category;

        if ($submission->sub_category != null) {
            $result .= ', ' . $submission->sub_category;
        }

        return $result;
    }
}

/**
 * Format individuals name from ontology.
 * 
 * @return string
 */
if (!function_exists('format_individual_name')) {
    function format_individual_name($name)
    {
        return ucwords(str_replace('_', ' ', $name));
    }
}

/**
 * Format lyrics for displaying in html.
 * 
 * @return string
 */
if (!function_exists('format_lyrics_html')) {
    function format_lyrics_html($lyrics)
    {
        return preg_replace("/\r\n|\r|\n/", '<br/>', $lyrics);
    }
}

/**
 * Join array of usages for displaying in html.
 * 
 * @return string
 */
if (!function_exists('format_usages_html')) {
    function format_usages_html($usages): ?string
    {
        if ($usages == null)
            return null;

        $result = null;

        foreach ($usages as $usage) {
            $result .= ucwords($usage->activity) . '<br/>';
        }

        return $result;
    }
}

/**
 * Format category add space between words.
 * 
 * @return string
 */
if(!function_exists('format_category')) {
    function format_category($category)
    {
        return preg_replace('/(?<!\ )[A-Z]/', ' $0', $category);
    }
}

/**
 * Removes blank lines from a string
 * 
 * @return string
 */
if (!function_exists('str_remove_blank_lines')) {
    function str_remove_blank_lines($string): string
    {
        if ($string == null) return null;
        return preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", PHP_EOL, $string);
    }
}

/**
 * Convert date string into timestamps
 * 
 * @return timestamps
 */
if (!function_exists('parse_date_string')) {
    function parse_date_string($dateString) {
        return Carbon::parse($dateString);
    }
}