<?php

namespace Placecompany\Erp\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use RLuders\JWTAuth\Classes\JWTAuth;

class GetUserController extends Controller
{
    /**
     * Send the forgot password request
     *
     * @return Illuminate\Http\Response
     */
    public function getUserAndGroups(JWTAuth $auth)
    {
        if (!$user = $auth->user()) {
            return response()->json(
                ['error' => 'user_not_found'],
                Response::HTTP_NOT_FOUND
            );
        }

        $user->has_groups = $user->groups->lists('code');

        return response()->json(compact('user'));
    }
}
