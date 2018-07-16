<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => '参数 :attribute must be accepted.',
    'active_url'           => '参数 :attribute 不是合法的网页地址.',
    'after'                => '参数 :attribute 需为一个晚于 :date 的日期.',
    'alpha'                => '参数 :attribute 只能包含英文字母.',
    'alpha_dash'           => '参数 :attribute 只能包含英文字母, 数字, 及中横线.',
    'alpha_num'            => '参数 :attribute 只能包含英文字母和数字.',
    'array'                => '参数 :attribute 需为数组.',
    'before'               => '参数 :attribute 需为一个早于 :date 的日期.',
    'between'              => [
        'numeric' => '参数 :attribute 的值需在 :min 和 :max 之间.',
        'file'    => '参数 :attribute 的大小需在 :min 和 :max KB 之间.',
        'string'  => '参数 :attribute 的长度需为 :min 和 :max 个字符.',
        'array'   => '参数 :attribute 需包含 :min 和 :max 个元素.',
    ],
    'boolean'              => '参数 :attribute 需为 true 或者 false.',
    'confirmed'            => '参数 :attribute confirmation does not match.',
    'date'                 => '参数 :attribute 为非法日期.',
    'date_format'          => '参数 :attribute 需符合日期格式 :format.',
    'different'            => '参数 :attribute and :other must be different.',
    'digits'               => '参数 :attribute 需为 :digits 个字符.',
    'digits_between'       => '参数 :attribute must be between :min and :max digits.',
    'distinct'             => '参数 :attribute field has a duplicate value.',
    'email'                => '参数 :attribute 需为合法的邮箱地址.',
    'exists'               => '参数 selected :attribute is invalid.',
    'filled'               => '参数 :attribute field is required.',
    'image'                => '参数 :attribute must be an image.',
    'in'                   => '参数 selected :attribute is invalid.',
    'in_array'             => '参数 :attribute field does not exist in :other.',
    'integer'              => '参数 :attribute 需为整数.',
    'ip'                   => '参数 :attribute must be a valid IP address.',
    'json'                 => '参数 :attribute must be a valid JSON string.',
    'max'                  => [
        'numeric' => '参数 :attribute 不能为超过 :max .',
        'file'    => '参数 :attribute 的大小不能超过 :max KB.',
        'string'  => '参数 :attribute 的长度不能超过 :max 个字符.',
        'array'   => '参数 :attribute 不能包含超过 :max 个变量.',
    ],
    'mimes'                => '参数 :attribute 只能为以下格式: :values.',
    'min'                  => [
        'numeric' => '参数 :attribute 不能为小于 :min .',
        'file'    => '参数 :attribute 的大小不能超过 :min KB.',
        'string'  => '参数 :attribute 的长度不能超过 :min 个字符.',
        'array'   => '参数 :attribute 不能包含超过 :min 个变量.',
    ],
    'not_in'               => '参数 selected :attribute is invalid.',
    'numeric'              => '参数 :attribute 需为数字.',
    'present'              => '参数 :attribute field must be present.',
    'regex'                => '参数 :attribute 的格式不合正则规范.',
    'required'             => '参数 :attribute 必传.',
    'required_if'          => '当 参数 :other 的值为 :value 时, 参数 :attribute 必填.',
    'required_unless'      => '参数 :attribute field is required unless :other is in :values.',
    'required_with'        => '参数 :attribute field is required when :values is present.',
    'required_with_all'    => '参数 :attribute field is required when :values is present.',
    'required_without'     => '参数 :attribute field is required when :values is not present.',
    'required_without_all' => '参数 :attribute field is required when none of :values are present.',
    'same'                 => '参数 :attribute and :other must match.',
    'size'                 => [
        'numeric' => '参数 :attribute must be :size.',
        'file'    => '参数 :attribute must be :size kilobytes.',
        'string'  => '参数 :attribute must be :size characters.',
        'array'   => '参数 :attribute must contain :size items.',
    ],
    'string'               => '参数 :attribute 需为字符串.',
    'timezone'             => '参数 :attribute must be a valid zone.',
    'unique'               => '参数 :attribute has already been taken.',
    'url'                  => '参数 :attribute 需为合法URL路径.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [],

];
