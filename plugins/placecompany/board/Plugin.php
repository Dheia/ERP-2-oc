<?php namespace Placecompany\Board;

use Backend\Facades\Backend;
use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public function registerComponents()
    {
        return [
            'Placecompany\Board\Components\Board' => 'Board',
        ];
    }

    /**
     * Registers the settings model for User Extended
     * @return array
     */
    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => '게시판 환경설정',
                'description' => '게시판 환경설정',
                'category'    => '게시판',
                'icon'        => 'icon-cog',
                'class'       => 'Placecompany\Board\Models\Settings',
                'order'       => 100,
                'keywords'    => 'security user extended',
                'permissions' => ['']
            ]
        ];
    }

    public function registerMailLayouts()
    {
        return [
            'placecompany.board::layout.default' => 'placecompany.board::layout.default',
        ];
    }

    public function registerMailTemplates()
    {
        return [
            'placecompany.board::mail.default' => '게시판 기본 메일 템플릿',
        ];
    }
}
