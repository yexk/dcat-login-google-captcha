<?php

namespace Yexk\LoginCaptcha;

use Dcat\Admin\Admin;
use Dcat\Admin\Extend\ServiceProvider as BaseServiceProvider;
use Dcat\Admin\Models\Administrator;
use Dcat\Admin\Support\Helper;
use Dcat\Admin\Traits\HasFormResponse;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;
use Yexk\LoginCaptcha\PHPGangsta\GoogleAuthenticator;

/**
 * 服务提供者.
 *
 * @create 2021-2-28
 */
class ServiceProvider extends BaseServiceProvider
{
    use HasFormResponse;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        Admin::booting(function () {
            $except = admin_base_path('auth/login');
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            // 匹配登陆get
            if (Helper::matchRequestPath('get:' . $except)) {
                $script = '
                ;(function() {
                    var captcha_tpl = \'<fieldset class="form-label-group form-group position-relative has-icon-left">\'
                        captcha_tpl += \'<input id="captcha" type="text" style="" class="form-control" name="captcha" placeholder="' . static::trans('captcha.enter_captcha') . '" required>\'
                        captcha_tpl += \'<div class="form-control-position">\'
                        captcha_tpl += \'<i class="feather icon-image"></i>\'
                        captcha_tpl += \'</div>\'
                        captcha_tpl += \'<label for="captcha">' . static::trans('captcha.captcha') . '</label>\'
                        captcha_tpl += \'<div class="help-block with-errors"></div>\'
                        captcha_tpl += \'</fieldset>\';
                    $(captcha_tpl).insertAfter($("#login-form fieldset.form-label-group").get(1));
                })();
                ';
                Admin::script($script);
            }

            // 匹配登陆post
            if (Helper::matchRequestPath('post:' . $except)) {
                $username = request()->input('username');
                $captcha = request()->input('captcha');

                $validator = Validator::make([
                    'captcha' => $captcha,
                ], [
                    'captcha' => 'required',
                ]);

                if ($validator->fails()) {
                    $this->returnError($validator);
                }

                // google 验证
                if (! $this->verifyGoogleCode($username, $captcha)) {
                    $this->returnError([
                        'captcha' => static::trans('captcha.captcha_error'),
                    ]);
                }
            }
        });
    }

    /**
     * 设置.
     */
    public function settingForm()
    {
        return new Setting($this);
    }

    /**
     * @param mixed $username
     * @param mixed $code
     */
    public static function verifyGoogleCode($username, $code)
    {
        $user = Administrator::where(['username' => $username])->first();
        if (! $user) {
            return false;
        }
        if ($user->is_open_google) {
            $ga = new GoogleAuthenticator();
            return $ga->verifyCode($user->google_secret, $code);
        }
        return true;
    }

    /**
     * 返回错误信息.
     * @param mixed $msg
     */
    protected function returnError($msg)
    {
        $response = $this->validationErrorsResponse($msg);
        throw new HttpResponseException($response);
    }
}
