<?php
namespace Lh\Workflow\Repositories;

use Lh\Workflow\Model\RunLog;


class LogRepo{
    /**
	 * 工作流审批日志记录
	 *
	 * @param  $uid 实例id
	 * @param  $run_id 运行的工作流id
	 * @param  $content 审批意见
	 * @param  $from_id 单据id
	 * @param  $from_table 单据表
	 * @param  $btn 操作按钮 ok 提交 back 回退 sing 会签  Send 发起
	 **/
	public static function addRunLog($uid,$run_id,$config,$btn)
	{
        $work_return ='';
        if($btn<>'Send' && $btn<>'SupEnd'){
            $work_return = WorkRepo::WorkApi($config);//在日志记录前加载节点钩子
        }
		 if (!isset($config['art'])) {
               $config['art'] = '';
         }
		$run_log = array(
                'uid'=>$uid,
				'run_flow'=>$config['flow_id']?? 0,
                'run_process'=>$config['run_process']?? 0,
                'flow_process'=>$config['flow_process']?? 0,
                'npid'=>$config['npid']?? 0,
				'from_id'=>$config['wf_fid'],
				'from_table'=>$config['wf_type'],
                'run_id'=>$run_id,
                'content'=>$config['check_con'],
                'work_info'=>$work_return,
				'art'=>$config['art'],
                'btn'=>$btn,//从 serialize 改用  json_encode 兼容其它语言
                'dateline'=>(!empty($config['log_time'])) ? $config['log_time'] : time(), //$config['log_time']迁移数据用
                'wad_day'=>$config['wad_day'] ?? '', //补填天数 项目逾期补填用
                'table_type'=>self::tableType($config['wf_type'],$config['wf_fid']) ?? ''
            );
		 //dd($run_log);
			 $run_log = RunLog::create($run_log);
			 if(!$run_log)
				{
					return  false;
				}
				return $run_log;
	}

	public static function tableType($table,$id){
	    //1,修改 2,终止
        if($table == 'adjust'){
            return \DB::table($table)->where('id',$id)->value('is_adjust');
        }
	    return '';
    }
}
