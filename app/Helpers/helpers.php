<?php

use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cookie;

/**
 * CUSTOM HELPER FUNCTIONS
 *
 * @package DashWind
 * @author Softnio
 * @version 1.0.0
 * @since 1.0
 *
 */

if (!function_exists('site_info')) {
    /**
     * Get site info with helper function
     *
     * @param $out
     * @return mixed
     * @version 1.0.0
     * @since 1.0
     */
    function site_info($out = 'name')
    {
        $output  = (!empty($out)) ? $out : 'name';
        $copyright = copyright(config('app.name'), date('Y'));

        $app_info = [
            'app' => config('app.name'),
            'desc' => config('app.desc'),
            'name' => config('app.site_name'),
            'email' => config('app.site_email'),
            'url' => url('/'),
            'url_app' => config('app.url'),
            'copyright' => $copyright,
            'vers' => config('app.version')
        ];

        return ($output == 'all') ? $app_info : Arr::get($app_info, $output, '');
    }
}


if (!function_exists('css_state')) {
    /**
     * Check if route exist or not
     *
     * @param $arr
     * @param $key
     * @return boolean
     * @version 1.0.0
     * @since 1.0
     */
    function css_state($arr, $key, $css = 'active', $empty = false)
    {
        if (is_array($arr)) {
            if ($empty) {
                return (Arr::has($arr, $key) && !Arr::get($arr, $key, null)) ? ' ' . $css : '';
            } else {
                return (Arr::has($arr, $key)) ? ' ' . $css : '';
            }
        }

        return '';
    }
}

if (!function_exists('has_route')) {
    /**
     * Check if route exist or not
     *
     * @param $name
     * @return boolean
     * @version 1.0.0
     * @since 1.0
     */
    function has_route($name)
    {
        return Route::has($name);
    }
}

if (!function_exists('is_route')) {
    /**
     * Check route to match current route
     *
     * @param $name
     * @param $parent false
     * @return boolean
     * @version 1.0.0
     * @since 1.0
     */
    function is_route($name, $parent = false)
    {
        $routeName = $name;
        if ($parent) {
            $routeName = (Str::contains($name, '.')) ? explode('.', $name) : $name;
            if (is_array($routeName) && count($routeName) > 1) {
                $routeName = str_replace(last($routeName), '*', $name);
            }
        }
        return request()->routeIs($routeName);
    }
}

if (!function_exists('get_initials')) {
    /**
     * Get user initial from name
     *
     * @param $name
     * @return $initial
     * @version 1.0.0
     * @since 1.0
     */
    function get_initials($name)
    {
        $words = explode(' ', $name);
        $letter1 = isset($words[0]) ? $words[0] : '';
        $letter2 = isset($words[1]) ? $words[1] : '';
        $initial = ($letter1 || $letter2) ? (substr($letter1, 0, 1) . substr($letter2, 0, 1)) : '';

        return $initial;
    }
}

if (!function_exists('last_word')) {
    /**
     * Get last word from string/name.
     *
     * @param $str
     * @return mixed|string
     * @version 1.0.0
     * @since 1.0
     */
    function last_word($str)
    {
        $words = explode(' ', $str);
        return array_pop($words);
    }
}

if (!function_exists('first_word')) {
    /**
     * Get first word from string/name.
     *
     * @param $str
     * @return mixed|string
     * @version 1.0.0
     * @since 1.0
     */
    function first_word($str)
    {
        $words = explode(' ', $str);
        return $words[0] ?? '';
    }
}

if (!function_exists('clear_ecache')) {
    /**
     * Clear Laravel Cache
     *
     * @version 1.0.0
     * @since 1.0
     */
    function clear_ecache()
    {
        Artisan::call('cache:clear');
    }
}

if (!function_exists('copyright')) {
    /**
     * Get copyright text with year.
     *
     * @param $name
     * @return mixed|string
     * @version 1.0.0
     * @since 1.0
     */
    function copyright($name = null, $year = null)
    {
        $name = ($name) ? $name : config('app.name');
        $year = empty($year) ? date('Y') : $year;
        return $year . " " . $name;
    }
}

if (!function_exists("dark_mode")) {
    /**
     * Get dark mode from cookie
     *
     * @return string
     * @version 1.0.0
     * @since 1.0
     */
    function dark_mode()
    {
        $mode = gcs('skin', 'light');
        return ($mode == 'dark') ? true : false;
    }
}

if (!function_exists("gcs")) {
    /**
     * Get cookie settings
     *
     * @param $name
     * @param $default false
     * @return mixed|$default
     * @version 1.0.0
     * @since 1.0
     */
    function gcs($name, $default = false, $key = 'app')
    {
        if (!empty($key) && $key == 'app') {
            $key =  strtolower(config('app.name')) . '_';
        } else {
            $key = empty($key) ? '' : $key;
        }

        $value = '';
        $name = ($name) ? $key . $name : false;

        if ($name) {
            $namekey = str_replace($key, '', $name);
            $value = data_get($_COOKIE, $namekey);

            return !empty($value) ? $value : $default;
        }

        return $default;
    }
}
