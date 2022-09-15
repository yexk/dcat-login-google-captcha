<?php

namespace Yexk\LoginCaptcha;

use Dcat\Admin\Extend\Setting as Form;

/**
 * 设置.
 */
class Setting extends Form
{
    /**
     * 设置标题.
     */
    public function title()
    {
        return $this->trans('captcha.setting');
    }

    /**
     * 设置表单.
     */
    public function form()
    {
    }

    /**
     * 格式化.
     */
    protected function formatInput(array $input)
    {
    }
}
