<?php

namespace Lh\Workflow;

use Illuminate\Support\ServiceProvider;
use Lh\Workflow\Controllers\WorkController;

class WorkProvider extends ServiceProvider
{
    //protected $defer = false; // 延迟加载服务
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //视图 视图模板的路径和扩展包名称
        $this->loadViewsFrom(__DIR__.'/views', 'Work');
        // 发布配置文件
        $this->publishes([
            __DIR__.'/views' => base_path('resources/views/vendor/work'),  // 发布视图目录到resources 下
            __DIR__.'/config/workflow.php' => config_path('workflow.php'), // 发布config文件
        ]);
        //发布前端资源
        // php artisan vendor:publish --tag=public --force 由于需要在每次包更新时覆盖前端资源，可以使用 --force 标识
        $this->publishes([
            __DIR__.'/assets' => public_path('vendor/workflow'),
        ], 'public');
        //注册路由
        $this->loadRoutesFrom(__DIR__.'/routes.php');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->singleton('work', function ($app) {
            return new WorkController($app['config']);
        });
    }

//    public function provides()
//    {
//        // 因为延迟加载 所以要定义 provides 函数 具体参考laravel 文档
//        return ['work'];
//     }
}
