<?php

namespace Placecompany\Erp\Tests\Classes;

use Placecompany\Erp\Classes\RestApi;
use PluginTestCase;
use System\Classes\PluginManager;
use Placecompany\Erp\Classes\NaverAdvertise;

class NaverAdvertiseTest extends PluginTestCase
{
    public function setUp()
    {
        parent::setUp();

        // Get the plugin manager
        $pluginManager = PluginManager::instance();

        // Register the plugins to make features like file configuration available
        $pluginManager->registerAll(true);

        // Boot all the plugins to test with dependencies of this plugin
        $pluginManager->bootAll(true);
    }

    public function tearDown()
    {
        parent::tearDown();

        // Get the plugin manager
        $pluginManager = PluginManager::instance();

        // Ensure that plugins are registered again for the next test
        $pluginManager->unregisterAll();
    }

    /**
     * @test
     * @group ignore
     */
    public function getRemainingBizMoney()
    {
        $api = new RestApi('https://api.naver.com', '01000000004b0988492fe2a1d8e8630839e17092f3e445448c0007fde23c7fd9e5a33b53be', 'AQAAAABLCYhJL+Kh2OhjCDnhcJLzMGYqki3yd7oQ3YuTJwAA2w==', '2041325');
        $naverAdvertise = new NaverAdvertise($api);
        $bizMoney = $naverAdvertise->getRemainingBizMoney();
        $this->assertNotNull($bizMoney);
    }
}
