<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------

if (file_exists(CMF_ROOT . "data/conf/route.php")) {
    $runtimeRoutes = include CMF_ROOT . "data/conf/route.php";
} else {
    $runtimeRoutes = [];
}
\think\Route::any('admin/article/:id', 'admin/article/index',[], ['id'=>'\d+$']);
\think\Route::any('portal/articles/:id', 'portal/articles/index',[], ['id'=>'\d+$']);
\think\Route::any('portal1/articles/:id', 'portal1/articles/index',[], ['id'=>'\d+$']);
\think\Route::any('portal2/articles/:id', 'portal2/articles/index',[], ['id'=>'\d+$']);
\think\Route::any('portal3/articles/:id', 'portal3/articles/index',[], ['id'=>'\d+$']);
\think\Route::any('portal4/articles/:id', 'portal4/articles/index',[], ['id'=>'\d+$']);
\think\Route::any('portal5/articles/:id', 'portal5/articles/index',[], ['id'=>'\d+$']);
\think\Route::any('wx2/articles/:id', 'wx2/articles/index',[], ['id'=>'\d+$']);
\think\Route::any('wx/articles/:id', 'wx/articles/index',[], ['id'=>'\d+$']);
return $runtimeRoutes;