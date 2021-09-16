<?php

namespace Config;

use App\Filters\AdminFilter;
use App\Filters\JWTAuthenticationFilter;
use App\Filters\ReviewerFilter;
use App\Filters\VerifiedEmailFilter;
use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\Honeypot;

class Filters extends BaseConfig
{
    /**
     * Configures aliases for Filter classes to
     * make reading things nicer and simpler.
     *
     * @var array
     */
    public $aliases = [
        'csrf'      => CSRF::class,
        'toolbar'   => DebugToolbar::class,
        'honeypot'  => Honeypot::class,
        'auth'      => JWTAuthenticationFilter::class,
        'admin'     => AdminFilter::class,
        'reviewer'  => ReviewerFilter::class,
        'verified'  => VerifiedEmailFilter::class,
    ];

    /**
     * List of filter aliases that are always
     * applied before and after every request.
     *
     * @var array
     */
    public $globals = [
        'before' => [
            // 'honeypot',
            // 'csrf',
        ],
        'after'  => [
            'toolbar',
            // 'honeypot',
        ],
    ];

    /**
     * List of filter aliases that works on a
     * particular HTTP method (GET, POST, etc.).
     *
     * Example:
     * 'post' => ['csrf', 'throttle']
     *
     * @var array
     */
    public $methods = [];

    /**
     * List of filter aliases that should run on any
     * before or after URI patterns.
     *
     * Example:
     * 'isLoggedIn' => ['before' => ['account/*', 'profiles/*']]
     *
     * @var array
     */
    public $filters = [
        'auth' => [
            'before' => [
                'api/auth/me',
                'api/settings/*',
                'api/users/*',
            ],
        ],
        'admin' => [
            'before' => [
                'api/admin/*'
            ],
        ],
        'reviewer' => [
            'before' => [
                'api/reviewers/*'
            ]
        ],
        'verified' => [
            'before' => [
                'api/exams',
                'api/exams/*',
                'api/requests',
                'api/requests/*',
                'api/groups',
                'api/groups/*'
            ]
        ]
    ];
}
