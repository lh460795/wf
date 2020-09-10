<?php
namespace Lh\Workflow\Repositories;

use Lh\Workflow\Model\Flow;
use Lh\Workflow\Model\Run;
use Lh\Workflow\Model\User;
use Lh\Workflow\Model\RunProcess;
use Lh\Workflow\Model\FlowProcess;
use Illuminate\Support\Facades\DB;
use Lh\Workflow\Model\RunLog;

class ProcessRepo{
    /**
	 * 根据ID获取流程信息
	 *
	 * @param $pid 步骤编号
	 */
	public static function getProcessInfo($pid,$run_id='')
	{
		$info = DB::table('wf_flow_process')
				->select('id','process_name','process_type','process_to','auto_person','auto_sponsor_ids','auto_role_ids','auto_sponsor_text','auto_role_text','range_user_ids','range_user_text','is_sing','is_back','wf_mode','wf_action','work_ids','work_text','flow_id')
				->find($pid);
		if($info->auto_person==3){ //办理人员
			$ids = explode(",",$info->range_user_text);
			$info->todo = ['ids'=>explode(",",$info->range_user_ids),'text'=>explode(",",$info->range_user_text)];
		}
		if($info->auto_person==4){ //办理人员
			$info->todo = $info->auto_sponsor_text;
		}
		if($info->auto_person==5){ //办理角色
			$info->todo = $info->auto_role_text;
		}
        if($info->auto_person==6){ //办理事务接受 新增
            $wf  =  Run::where('id',$run_id)->first();
            $user_id = InfoRepo::GetBillValue($wf['from_table'],$wf['from_id'],$info->work_text);
            $user_info = UserRepo::GetUserInfo($user_id);
            $info->user_info= $user_info;
            $info->todo= $user_info->username;
        }
        //dd($info);
		return $info;
	}
	/**
	 * 同步步骤信息
	 *
	 * @param $pid 步骤编号
	 */
	public static function GetProcessInfos($ids,$run_id)
	{
		$info = DB::table('wf_flow_process')
				->select('id','process_name','process_type','process_to','auto_person','auto_sponsor_ids','auto_role_ids','auto_sponsor_text','auto_role_text','range_user_ids','range_user_text','is_sing','is_back','wf_mode','wf_action','work_ids','work_text')
				->where('id','in',$ids)
				->get();
		foreach($info as $k=>$v){
			if($v['auto_person']==3){ //办理人员
				$ids = explode(",",$info['range_user_text']);
				$info[$k]['todo'] = ['ids'=>explode(",",$v['range_user_ids']),'text'=>explode(",",$v['range_user_text'])];
			}
			if($v['auto_person']==4){ //办理人员
				$info[$k]['todo'] = $v['auto_sponsor_text'];
			}
			if($v['auto_person']==5){ //办理角色
				$info[$k]['todo'] = $v['auto_role_text'];
			}
            if($v['auto_person']==6){ //办理角色
                $wf  =  Run::where('id',$run_id)->first();
                $user_id = InfoRepo::GetBillValue($wf['from_table'],$wf['from_id'],$info[$k]['work_text']);
                $user_info = UserRepo::GetUserInfo($user_id);
                $info['user_info']= $user_info;
                $info[$k]['todo']= $user_info['username'];
            }
		}

		return $info;
	}
	/**
	 * 获取下个审批流信息
	 *
	 * @param $wf_type 单据表
	 * @param $wf_fid  单据id
	 * @param $pid   流程id
	 **/
	public static function GetNexProcessInfo($wf_type,$wf_fid,$pid,$run_id,$premode='')
	{
        if($pid==''){
            return [];
        }
		$nex = DB::table('wf_flow_process')->find($pid);
		//先判断下上一个流程是什么模式
		if($nex->process_to !=''){
		$nex_pid = explode(",",$nex->process_to);
		$out_condition = json_decode($nex->out_condition,true);
		//var_dump($wf_fid);die();
		//dump($out_condition);
			//加入同步模式 2为同步模式
			/*
			 * 2019年1月28日14:30:52
			 *1、加入同步模式
			 *2、先获取本步骤信息
			 *3、获取本步骤的模式
			 *4、根据模式进行读取
			 *5、获取下一步骤需要的信息
			 **/
			switch ($nex->wf_mode){
			case 0:
			  $process = self::GetProcessInfo($nex_pid,$run_id);
			  break;
			case 1:
//                var_dump($wf_fid);die();
				//多个审批流 这个有bug  进行修复
				foreach($out_condition as $key=>$val){
					$where =implode(",",$val['condition']);
                    $where = str_replace(',',' ',$where);
					//dump($where);die();
					//根据条件寻找匹配符合的工作流id
                    //DB::connection()->enableQueryLog();
					$info = DB::table($wf_type)->whereRaw($where)->find($wf_fid);
                    //dd(DB::getQueryLog());
					if($info){
						$nexprocessid = $key; //获得下一个流程的id
						break;
					}
				}
				$process = self::GetProcessInfo($nexprocessid,$run_id);
			   break;
			case 2:
				$process = self::GetProcessInfos($nex_pid,$run_id);
			  break;
			}
		}else{
			$process = ['auto_person'=>'','id'=>'','process_name'=>'END','todo'=>'结束'];
		}

		return $process;
	}
	/**
	 * 获取前步骤的流程信息
	 *
	 * @param $runid
	 */
	public static function getPreProcessInfo($runid)
	{
	    //修复原来tp 写法导致bug
		$pre = [];
		$pre_n = DB::table('wf_run_process')->where('id',$runid)->first();

		//获取本流程中小于本次ID的步骤信息
        //dump($runid);
        $prearray = [];
        if(!empty($pre_n)){
                $where=[
                    ['run_flow','=',$pre_n->run_flow],
                    ['run_id','=',$pre_n->run_id],
                    ['id','<',$pre_n->id],
                ];
                $pre_p = DB::table('wf_run_process')
                 ->where($where)
                 ->select('run_flow_process')->pluck('run_flow_process');

            //遍历获取小于本次ID中的相关步骤
            foreach(collect($pre_p)->toArray() as $k=>$v){
                $pre[] = Db::table('wf_flow_process')->where('id',$v)->first();
            }

            //$prearray = [];
            if(count($pre)>=1){
                //$prearray[0] = '退回制单人修改';
                foreach($pre as $k => $v){
                    if($v->auto_person==4){ //办理人员
                        $todo = $v->auto_sponsor_text;
                    }
                    if($v->auto_person==5){ //办理角色
                        $todo = $v->auto_role_text;
                    }
                    if($v->auto_person==6){ //办理角色
                        $wf  =  Run::where('id',$pre_n->run_id)->first();
                        $user_id = InfoRepo::GetBillValue($wf['from_table'],$wf['from_id'],$v->work_text);
                        $user_info = UserRepo::GetUserInfo($user_id);
                        $todo= $user_info->username;
                    }
                    $prearray[$v->id] = $v->process_name.'('.$todo.')';
                }
            }else{
                //$prearray[0] = '退回制单人修改';
            }

        }
		return $prearray;
	}
	/**
	 * 获取前步骤的流程信息
	 *
	 * @param $runid
	 */
	public static function getRunProcess($pid,$run_id)
	{
		$pre_n = RunProcess::where('run_id',$run_id)->where('run_flow_process',$pid)->get();
		return $pre_n;
	}

	/**
	 * 获取第一个流程
	 *
	 * @param $wf_id
	 */
	public static function getWorkflowProcess($wf_id)
	{
		$flow_process = FlowProcess::where('flow_id',$wf_id)->get();
		//找到 流程第一步
        $flow_process_first = array();
        foreach($flow_process as $value)
        {
            if($value['process_type'] == 'is_one')
            {
                $flow_process_first = $value;
                break;
            }
        }
		if(!$flow_process_first)
        {
            return  false;
        }
		return $flow_process_first;
	}
	/**
	 * 流程日志
	 *
	 * @param $wf_fid
	 * @param $wf_type
	 */
	public static function runLog($wf_fid,$wf_type)
	{
		$type = ['Send'=>'流程发起','ok'=>'同意提交','Back'=>'退回修改','SupEnd'=>'终止流程','Sing'=>'会签提交','sok'=>'会签同意','SingBack'=>'会签退回','SingSing'=>'会签再会签'];
		$run_log = RunLog::where('from_id',$wf_fid)->where('from_table',$wf_type)->get();
		foreach($run_log as $k=>$v)
        {
			$user = User::find($v['uid']);
			$run_log[$k]['user'] = $user->username;
			$run_log[$k]['btn'] =$type[$v['btn']] ?? '';
        }
		return $run_log;
	}

    /**
     * 阻止重复提交
     *
     * @param $id
     */
    public static function run_check($id)
    {
        return Db::table('wf_run_process')->where('id',$id)->value('status');

    }
}
