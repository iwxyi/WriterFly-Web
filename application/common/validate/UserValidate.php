<?php
namespace app\common\validate;
use think\Validate;

class User extends Validate
{
	protected $rule = [
		'username' => 'require|chsDash|length:4,20',
		'password' => 'require|chsDash|length:1,20',
		'nickname' => 'chsDash|length:2,10', // 中文数字字母下划线
	];
}