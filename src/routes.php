<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


//工作流列表
Route::any('/wf/wfindex','Lh\Workflow\Controllers\WorkController@wfindex');
Route::any('/wf/wfjk','Lh\Workflow\Controllers\WorkController@wfjk');
Route::any('/wf/super_user/{type}','Lh\Workflow\Controllers\WorkController@super_user');
Route::any('/wf/super_role','Lh\Workflow\Controllers\WorkController@super_role');
//流程设计器
Route::any('/wf/wfdesc/{flow_id}','Lh\Workflow\Controllers\WorkController@wfdesc');
Route::any('/wf/wfchange/{id?}/{status?}','Lh\Workflow\Controllers\WorkController@wfchange');
Route::any('/wf/delete_process','Lh\Workflow\Controllers\WorkController@delete_process');
Route::any('/wf/del_allprocess','Lh\Workflow\Controllers\WorkController@del_allprocess');
Route::any('/wf/add_process','Lh\Workflow\Controllers\WorkController@add_process');
Route::any('/wf/save_canvas','Lh\Workflow\Controllers\WorkController@save_canvas');
//工作流添加
Route::any('/wf/wfadd','Lh\Workflow\Controllers\WorkController@wfadd');
//工作流修改
Route::any('/wf/wfedit/{id}','Lh\Workflow\Controllers\WorkController@wfedit');
//步骤属性设计
Route::any('/wf/wfatt','Lh\Workflow\Controllers\WorkController@wfatt');
Route::any('/wf/save_attribute','Lh\Workflow\Controllers\WorkController@save_attribute');
Route::any('/wf/checkflow','Lh\Workflow\Controllers\WorkController@Checkflow');
//用户查询
Route::any('/wf/super_get','Lh\Workflow\Controllers\WorkController@super_get');
Route::any('/wf/btn','Lh\Workflow\Controllers\WorkController@btn');
Route::any('/wf/status','Lh\Workflow\Controllers\WorkController@status');
//流程启动  
Route::any('/wf/wfstart','Lh\Workflow\Controllers\WorkController@wfstart');
Route::any('/wf/start_save','Lh\Workflow\Controllers\WorkController@start_save');
Route::any('/wf/wfcheck','Lh\Workflow\Controllers\WorkController@wfcheck');
Route::any('/wf/do_check_save','Lh\Workflow\Controllers\WorkController@do_check_save');
//附件接口
Route::any('/wf/ajax_back','Lh\Workflow\Controllers\WorkController@ajax_back');
Route::any('/wf/wfup','Lh\Workflow\Controllers\WorkController@wfup');
Route::any('/wf/wfend','Lh\Workflow\Controllers\WorkController@wfend');
Route::any('/wf/wfupsave','Lh\Workflow\Controllers\WorkController@wfupsave');