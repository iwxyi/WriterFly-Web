<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

/* return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],
]; */
use think\Route;
Route::rule('download','index/index/download');
Route::rule('d','index/index/download');
Route::rule('rank','index/index/rank');
Route::rule('r','index/index/rank');
Route::rule('share','index/novel/publishedChapters');
Route::rule('s','index/novel/publishedChapters');
Route::rule('c','index/novel/publishedChapter/');