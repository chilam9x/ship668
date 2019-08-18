<?php
namespace App\Http\Middleware;
use Auth;
use Closure;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
class VerifyJWTToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            try {
                $user = JWTAuth::parseToken()->authenticate();
            }
            catch (JWTException $e) {
                // Fix for jwt libraries
                $token = $request->get('token', false);
                $user = JWTAuth::setToken($token)->authenticate();
            }

            if (!$user) {
                return response(['code_token'=>0,'status'=>401, 'msg' => 'Phiên làm việc của bạn đã hết hạn. Vui lòng đăng nhập lại để tiếp tục'], 200);
            }

            Auth::login($user);
            return $next($request);
        }

        catch (TokenInvalidException $e) {
            return response(['code_token'=>0,'status'=>401, 'msg' => 'Phiên làm việc của bạn đã hết hạn. Vui lòng đăng nhập lại để tiếp tục'], 200);
        }   
        catch (JWTException $e) {
            return response(['code_token'=>0,'status'=>401, 'msg' => 'Phiên làm việc của bạn đã hết hạn. Vui lòng đăng nhập lại để tiếp tục'], 200);
        }
        catch (TokenExpiredException $e) {
            return response(['code_token'=>0,'status'=>401, 'msg' => 'Phiên làm việc của bạn đã hết hạn. Vui lòng đăng nhập lại để tiếp tục'], 200);
        }
    }
}