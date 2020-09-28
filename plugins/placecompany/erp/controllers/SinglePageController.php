<?php namespace Placecompany\Erp\Controllers;


use Cms\Classes\Controller;
use Cms\Classes\Theme;
use Illuminate\Support\Facades\App;
use JavaScript;
use Placecompany\Erd\Classes\MenuItem;
use Placecompany\Erd\Classes\MenuManager;
use RainLab\User\Facades\Auth;
use RainLab\User\Models\User;
use System\Twig\Extension;
use System\Twig\Engine as TwigEngine;

class SinglePageController extends Controller
{

    public function displaySPA()
    {
//        /**
//         * @var User $currentUser
//         */
//        $currentUser = Auth::getUser();
//
//        if($currentUser) {
//            $menuManager = new MenuManager();
//            $menuManager->setUser($currentUser);
//            $menuManager->addMenus([
//                new MenuItem([
//                    'group_requirements' => [],
//                    'permission_requirements' => ['superuser'],
//                    'label'=>'Dashboard',
//                    'nav_type' => MenuItem::$NAV_TYPE_NAV,
//                    'icon'=>'dashboard',
//                    'route_type'=>'vue',
//                    'route_name'=>'dashboard'
//                ]),
//
//                new MenuItem([
//                    'group_requirements' => [],
//                    'permission_requirements' => ['superuser'],
//                    'label'=>'User',
//                    'nav_type' => MenuItem::$NAV_TYPE_NAV,
//                    'icon'=>'person',
//                    'route_type'=>'vue',
//                    'route_name'=>'users.list'
//                ]),
//                new MenuItem([
//                    'group_requirements' => [],
//                    'permission_requirements' => ['superuser'],
//                    'label'=>'Files',
//                    'nav_type' => MenuItem::$NAV_TYPE_NAV,
//                    'icon'=>'cloud_circle',
//                    'route_type'=>'vue',
//                    'route_name'=>'files'
//                ]),
//                new MenuItem([
//                    'group_requirements' => [],
//                    'permission_requirements' => ['superuser'],
//                    'label'=>'Settings',
//                    'nav_type' => MenuItem::$NAV_TYPE_NAV,
//                    'icon'=>'settings',
//                    'route_type'=>'vue',
//                    'route_name'=>'settings'
//                ]),
//                new MenuItem([
//                    'group_requirements' => [],
//                    'permission_requirements' => ['user'],
//                    'label'=>'계약관리',
//                    'nav_type' => MenuItem::$NAV_TYPE_NAV,
//                    'icon'=>'manage',
//                    'route_type'=>'vue',
//                    'route_name'=>'signs'
//                ]),
//                new MenuItem([
//                    'nav_type' => MenuItem::$NAV_TYPE_DIVIDER
//                ])
//            ]);
//
//            $menus = $menuManager->getFiltered();
//        }
//
//        return view('index', $menus);
    }
}
