<?php
/**
*+------------------
* Tpflow 普通提交工作流
*+------------------
* Copyright (c) 2006~2018 http://cojz8.cn All rights reserved.
*+------------------
* Author: guoguo(1838188896@qq.com)
*+------------------
*/
namespace Lh\Workflow\Service\Task;

use Lh\Workflow\Model\Run;
use Lh\Workflow\Model\RunProcess;
use Lh\Workflow\Repositories\FlowRepo;
use Lh\Workflow\Repositories\ProcessRepo;
use Lh\Workflow\Repositories\InfoRepo;
use Lh\Workflow\Repositories\LogRepo;



class TaskFlow{
	/**
	 * 任务执行
	 *
	 * @param  $config 参数信息
	 * @param  $uid  用户ID
	 */
	public static function doTask($config,$uid) {
		//任务全局类
		$wf_title = $config['wf_title'];
		$npid = $config['npid'];//下一步骤流程id
		$run_id = $config['run_id'];//运行中的id
		$run_process = $config['run_process'];//运行中的process
		if($config['sup']=='1'){
			$check_con = '[管理员代办]'.$config['check_con'];
			$config['check_con'] = '[管理员代办]'.$config['check_con'];
		}else{
			$check_con = $config['check_con'];
		}
		$submit_to_save = $config['submit_to_save'];
		if(isset($config['todo'])){
			$todo = $config['todo'];
		}else{
			$todo = '';
		}
        if($config['wf_mode']==2){
            //$run_info = Run::find($config['run_id']);
            $info_list = RunProcess::
                where('run_id',$config['run_id'])
                ->where('status',0)
                ->where('id','!=',$config['run_process'])
                ->get();
            if(count($info_list)>0){
                foreach($info_list as $k=>$v){
                    $npids[] = $v['run_flow_process'];
                }
                $npid = implode(",", $npids);
            }
		}
        //dd($npid);
		if($npid != ''){//判断是否为最后
            //结束流程 添加uid 更新RunProcess uid 值
            $end = FlowRepo::end_process($run_process,$check_con,$uid);
            if(!$end){
                return ['msg'=>'结束流程错误！！！','code'=>'-1'];
            }
            /*
             * 2019年1月27日
             * 同步模式下，只写入记录
             */
            if($config['wf_mode']!=2){
                //更新单据信息
                FlowRepo::up($run_id,$npid);
                //记录下一个流程->消息记录
                //dd(222);
                self::run($config,$uid,$todo);
            }else{
                //日志记录
                $run_log = LogRepo::addRunLog($uid,$config['run_id'],$config,'ok');
                if(!$run_log){
                        return ['msg'=>'消息记录失败，数据库错误！！！','code'=>'-1'];
                }
            }
		}else{
				//结束该流程
				$end = FlowRepo::end_flow($run_id);
				$end = FlowRepo::end_process($run_process,$check_con,$uid);
				$run_log = LogRepo::addRunLog($uid,$run_id,$config,'ok');
				if(!$end){
					return ['msg'=>'结束流程错误！！！','code'=>'-1'];
				}
			//更新单据状态
			$bill_update = InfoRepo::updateBill($config['wf_fid'],$config['wf_type'],2);
			if(!$bill_update){
				return ['msg'=>'流程步骤操作记录失败，数据库错误！！！','code'=>'-1'];
			}
			//消息通知发起人
		}
        return ['msg'=>'success!','code'=>'0'];
	}
	/**
	 *运行记录
	 *
	 *@param $run_flow_process 工作流ID
	 **/
	public static function run($config,$uid,$todo)
	{

		$nex_pid = explode(",",$config['npid']);
		foreach($nex_pid as $v){
			$wf_process = ProcessRepo::getProcessInfo($v,$config['run_id']);
			//添加流程步骤日志
			$wf_process_log = InfoRepo::addWorkflowProcess($config['flow_id'],$wf_process,$config['run_id'],$uid,$todo);
		}
		if(!$wf_process_log){
				return ['msg'=>'流程步骤操作记录失败，数据库错误！！！','code'=>'-1'];
			}
		//日志记录
		$run_log = LogRepo::addRunLog($uid,$config['run_id'],$config,$config['submit_to_save']);
		if(!$run_log){
            return ['msg'=>'消息记录失败，数据库错误！！！','code'=>'-1'];
        }
	}
}
