<?php namespace Placecompany\BoardComment;

use Backend;
use Cms\Classes\Controller;
use System\Classes\PluginBase;

/**
 * board-comments Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * @var array Plugin dependencies
     */
    public $require = ['Placecompany.Board'];

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => '게시판 댓글 플러그인',
            'description' => '게시판 댓글 플러그인',
            'author'      => 'placecompany',
            'icon'        => 'icon-leaf'
        ];
    }

    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {
        \Event::listen('rainlab.user.register', function($user) {
            dd(123);
        });
    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return [
            'Placecompany\BoardComment\Components\BoardComment' => 'BoardComment',
        ];
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [];
    }

    /**
     * Registers back-end navigation items for this plugin.
     *
     * @return array
     */
    public function registerNavigation()
    {
        return [];
    }
}
