<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class ApiController extends BaseController
{
  use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

  protected $user;

  public function __construct() {
    date_default_timezone_set('Asia/Ho_Chi_Minh');

    try{
      $this->user = JWTAuth::parseToken()->authenticate();
    }
    catch(JWTException $e) {
      $this->user = null;
    }
    catch(TokenExpiredException $e) {
      $this->user = null;
    }
    catch(UnauthorizedHttpException $e) {
      $this->user = null;
    }
  }

  // Uniform the response
  public function apiRes($status, $message, $data, $error, $code, $datatype=1)
  {
    $apiResp = [];
    $apiResp['code_token'] = 1;
    $apiResp['datatype'] = $datatype;
    $apiResp['status'] = $status ? 200 : 404;
    $apiResp['data'] = $data;
    $apiResp['msg'] = $message;
    $apiResp['errors'] = $error;

    //return response($apiResp, $code ? $code : ($status ? 200 : 500));
    // Mobile library friendly with 200 response only: https://github.com/Alamofire/Alamofire
    return response($apiResp, $code ? $code : 200);
  }

  // Simple response success with data
  public function apiOk($data, $datatype=1)
  {
    return $this->apiRes(true, 'success', $data, null, null, $datatype);
  }

  // Some of common errors resposnse
  public function apiError($msg)
  {
    return $this->apiRes(false, $msg, null, null, null);
  }
  public function apiErrorWithCode($msg, $code)
  {
    return $this->apiRes(false, $msg, null, null, $code);
  }
  public function apiErrorDetails($msg, $errors, $code)
  {
    return $this->apiRes(false, $msg, null, $errors, $code);
  }
  public function apiErrorWithStatus($status, $message, $data = null, $error = null, $code = null, $datatype=1)
  {
    $apiResp = [];
    $apiResp['code_token'] = 1;
    $apiResp['datatype'] = $datatype;
    $apiResp['status'] = $status;
    $apiResp['data'] = $data;
    $apiResp['msg'] = $message;
    $apiResp['errors'] = $error;

    //return response($apiResp, $code ? $code : ($status ? 200 : 500));
    // Mobile library friendly with 200 response only: https://github.com/Alamofire/Alamofire
    return response($apiResp, $code ? $code : 200);
  }
}
