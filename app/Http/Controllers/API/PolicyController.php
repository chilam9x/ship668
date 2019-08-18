<?php
namespace App\Http\Controllers\Api;

use function dd;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Models\Notification;
use App\Models\NotificationUser;
use App\Models\Policy;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use DB;

class PolicyController extends ApiController
{
    public function getPolicy(Request $req) {
        $policy = Policy::where('id', '>', 0)->first();
        return $this->apiOk($policy);
    }
}