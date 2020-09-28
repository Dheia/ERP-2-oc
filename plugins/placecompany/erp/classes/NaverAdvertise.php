<?php
namespace Placecompany\Erp\Classes;

use Placecompany\Erp\Classes\NaverSearchAD\RestApi;

class NaverAdvertise {
    public $api;
    private $signs;

    public function __construct(RestApi $api, Sign $signs)
    {
        $this->api = $api;
        $this->signs = $signs;
    }

    public function getRemainingBizMoney() {
        $response = $this->api->GET("/billing/bizmoney");
        return $response["bizmoney"];
    }

    public function getBizMoneyCost() {
        $response = $this->api->GET("/billing/bizmoney/cost", [
            'searchStartDt' => '20200918',
            'searchEndDt' => '20200918',
        ]);

        return $response;
    }
}
