<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Artisan;

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

// ========================================
// ROLE & PERMISSION HELPER FUNCTIONS
// ========================================

if (!function_exists('user_has_role')) {
    /**
     * Vérifier si l'utilisateur connecté a un rôle spécifique
     *
     * @param string $role
     * @return bool
     * @version 1.0.0
     * @since 1.0
     */
    function user_has_role(string $role): bool
    {
        return Auth::check() && Auth::user()->hasRole($role);
    }
}

if (!function_exists('user_has_any_role')) {
    /**
     * Vérifier si l'utilisateur connecté a au moins un des rôles spécifiés
     *
     * @param array $roles
     * @return bool
     * @version 1.0.0
     * @since 1.0
     */
    function user_has_any_role(array $roles): bool
    {
        return Auth::check() && Auth::user()->hasAnyRole($roles);
    }
}

if (!function_exists('is_superadmin')) {
    /**
     * Vérifier si l'utilisateur connecté est un superadmin
     *
     * @return bool
     * @version 1.0.0
     * @since 1.0
     */
    function is_superadmin(): bool
    {
        return user_has_role('superadmin');
    }
}

if (!function_exists('can_access_scolarites')) {
    /**
     * Vérifier si l'utilisateur peut accéder aux scolarités
     *
     * @return bool
     * @version 1.0.0
     * @since 1.0
     */
    function can_access_scolarites(): bool
    {
        return is_superadmin();
    }
}

if (!function_exists('can_access_traitements')) {
    /**
     * Vérifier si l'utilisateur peut accéder aux traitements
     *
     * @return bool
     * @version 1.0.0
     * @since 1.0
     */
    function can_access_traitements(): bool
    {
        return user_has_any_role(['superadmin', 'enseignant', 'secretaire']);
    }
}

if (!function_exists('can_access_parametres')) {
    /**
     * Vérifier si l'utilisateur peut accéder aux paramètres
     *
     * @return bool
     * @version 1.0.0
     * @since 1.0
     */
    function can_access_parametres(): bool
    {
        return is_superadmin();
    }
}

if (!function_exists('get_user_role_badge_class')) {
    /**
     * Obtenir la classe CSS pour le badge du rôle
     *
     * @param string $roleName
     * @return string
     * @version 1.0.0
     * @since 1.0
     */
    function get_user_role_badge_class(string $roleName): string
    {
        return match($roleName) {
            'superadmin' => 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
            'enseignant' => 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
            'secretaire' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'
        };
    }
}

if (!function_exists('get_user_roles_as_string')) {
    /**
     * Obtenir les rôles de l'utilisateur sous forme de chaîne
     *
     * @param string $separator
     * @return string
     * @version 1.0.0
     * @since 1.0
     */
    function get_user_roles_as_string(string $separator = ', '): string
    {
        if (!Auth::check()) {
            return 'Invité';
        }

        $roles = Auth::user()->roles->pluck('name')->toArray();

        if (empty($roles)) {
            return 'Aucun rôle';
        }

        return implode($separator, array_map('ucfirst', $roles));
    }
}

if (!function_exists('has_access_to_route')) {
    /**
     * Vérifier si l'utilisateur a accès à une route spécifique selon les permissions
     *
     * @param string $routeName
     * @return bool
     * @version 1.0.0
     * @since 1.0
     */
    function has_access_to_route(string $routeName): bool
    {
        if (!Auth::check()) {
            return false;
        }

        // Routes publiques (accessibles à tous)
        $publicRoutes = ['dashboard'];

        // Routes scolarités (superadmin uniquement)
        $scolariteRoutes = [
            'unite_e', 'add_ue', 'edit_ue',
            'students', 'add_etudiant', 'edit_etudiant',
            'salles.index',
            'examens.index', 'examens.create', 'examens.edit', 'examens.reset'
        ];

        // Routes traitements (superadmin, enseignant, secretaire)
        $traitementRoutes = [
            'copies.index', 'copies.corbeille',
            'manchettes.index', 'manchettes.corbeille',
            'resultats.fusion', 'resultats.verification', 'resultats.finale'
        ];

        // Routes paramètres (superadmin uniquement)
        $parametreRoutes = [
            'setting.index', 'setting.user_management', 'setting.annee_universite',
            'setting.session_examen', 'setting.roles_permission'
        ];

        // Vérifier l'accès selon le type de route
        if (in_array($routeName, $publicRoutes)) {
            return true;
        }

        if (in_array($routeName, $scolariteRoutes)) {
            return can_access_scolarites();
        }

        if (in_array($routeName, $traitementRoutes)) {
            return can_access_traitements();
        }

        if (in_array($routeName, $parametreRoutes)) {
            return can_access_parametres();
        }

        // Par défaut, autoriser l'accès si la route n'est pas dans la liste
        return true;
    }
}
