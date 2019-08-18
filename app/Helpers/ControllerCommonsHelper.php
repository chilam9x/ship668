<?php

namespace App\Helpers;

trait ControllerCommonsHelper
{


  // Uniform the response
  public function apiRes($status, $message, $data, $error, $code, $datatype=1)
  {
    $apiResp = [];
    $apiResp['status'] = $status ? 'success' : 'fail';
    $apiResp['data'] = $data;
    $apiResp['msg'] = $message;
    $apiResp['errors'] = $error;
    $apiResp['datatype'] = $datatype;

    return response($apiResp, $code ? $code : ($status ? 200 : 500));
  }

  // Simple response success with data
  public function apiOk($data, $datatype=1)
  {
    return $this->apiRes(true, '', $data, null, null, 200, $datatype);
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


}
