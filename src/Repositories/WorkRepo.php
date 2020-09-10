<?php
namespace Lh\Workflow\Repositories;

use Lh\Workflow\Model\WorkInfo;
use Illuminate\Support\Facades\DB;

/**
 * 节点事务处理
 * @package Lh\Workflow\Repositories
 */
class WorkRepo{
    /**
     * 节点事务接口
     *
     * @param  $config 参数
     **/
    public static function WorkApi($config)
    {
        $sql_return = 'null';
        $msg_return = 'null';
        //取出当前运行的步骤ID
        $run_flow_process = Db::table('wf_run_process')->where('id',$config['run_process'])->value('run_flow_process');
        //获取当前步骤版本ID，对应的所有信息
        $flow_process_info = Db::table('wf_flow_process')->where('id',$run_flow_process)->first();
        if(!$flow_process_info){
            return 'flow_process_info err!';
        }

        if($flow_process_info->work_sql <> ''){
            $sql_return = self::WorkSql($config,$flow_process_info);
        }
        if($flow_process_info->work_msg <> ''){
            $msg_return= self::WorkMsg($config,$flow_process_info);
        }
        return 'work_sql:'.$sql_return.'|work_msg:'.$msg_return;

    }
    /**
     * 审批事务执行处理
     *
     **/
    public static function WorkSql($config,$flow_process_info)
    {
        //dd($flow_process_info);
        $new_work_sql=str_replace(['@from_id','@run_id','@check_con'],[$config['wf_fid'],$config['run_id'],$config['check_con']],$flow_process_info->work_sql);        //使用函数处理字符串
        try{
            //考虑到有多个sql的情况 分批执行
            $sql_query = explode(';',$new_work_sql);
            foreach ($sql_query as $item=>$value){
                if(!empty($value)){
                    //执行原生sql tp写法是 Db::query
                    $work_return = Db::statement($value);
                }
            }
        }catch(\Exception $e){
            $work_return = 'SQL_Err:'.$new_work_sql;
        }
        $insert_data = [
            'type'=>"work_sql",
            'bill_info'=>json_encode($config),
            'data'=>$new_work_sql,
            'info'=>$work_return,
            'created_at'=>date('Y-m-d h:i:s'),
            'updated_at'=>date('Y-m-d h:i:s')
        ];
        $result = WorkInfo::insertGetId($insert_data);
        if(!$result){
            return  '-1';
        }
        return $result;
    }
    /**
     * 消息转换
     *
     **/
    public static function WorkMsg($config,$flow_process_info)
    {
        $new_work_msg=str_replace(['@from_id','@run_id','@check_con'],[$config['wf_fid'],$config['run_id'],$config['check_con']],$flow_process_info->work_msg);        //使用函数处理字符串
        $insert_data = [
            'type'=>'work_msg',
            'bill_info'=>json_encode($config),
            'data'=>$new_work_msg,
            'info'=>'success',
            'created_at'=>date('Y-m-d h:i:s'),
            'updated_at'=>date('Y-m-d h:i:s')
        ];
        return WorkInfo::insertGetId($insert_data);
    }
}
