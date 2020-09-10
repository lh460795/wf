<?php
namespace Lh\Workflow\Model;

use Illuminate\Database\Eloquent\Model;
use App\Work\Model\Run;


class FlowProcess extends Model{
    protected $table = 'wf_flow_process';
    protected $guarded = [];

    //通过流程ID 以及步骤名称 获取步骤ID
    public static function getprocessId($flow_id,$process_name){
        return self::where([['flow_id', '=', $flow_id],['process_name', '=', $process_name]])->select('id')->first();
    }
    //通过当前用户id 以及步骤名称 获取步骤ID
    public static function getprocessIdByuid($uid,$process_name){
        return self::where([['auto_sponsor_ids', '=', $uid],['process_name', '=', $process_name]])->select('id')->first();
    }

    //通过当前用户id 以及步骤名称 获取步骤IDS $process_name_array 有多角色时
    public static function getprocessIdByuids($uid=0,$process_name,$process_name_array=[]){
        if($uid!=0){
            if(!empty($process_name_array)){
                return self::where([['auto_sponsor_ids', '=', $uid]])
                    ->whereIn('process_name', $process_name_array)
                    ->select('id')->pluck('id');
            }else{
                return self::where([['auto_sponsor_ids', '=', $uid],['process_name', '=', $process_name]])->select('id')->pluck('id');
            }
        }else{
            return self::where([['process_name', '=', $process_name]])->select('id')->pluck('id');
        }

    }

    //审核中条件
    public static function getprocessIdShz($uid,$process_name){
        return self::where([['auto_sponsor_ids', '!=', $uid],['process_name', '!=', $process_name],['process_name', '!=', '系统步骤']])->select('id')->pluck('id');
    }
    // 经过当前用户审核入库的所有在建项目
    public static function getprocessIdsByflow_id($uid,$process_name){
        $flow_info = self::where([['auto_sponsor_ids', '=', $uid],['process_name', '=', $process_name]])->get(['flow_id'])->toArray();
        $res=[];
        foreach ($flow_info as $key => $value) {
            $res[] = $value['flow_id'];
        }
        //dd($flow_info);([['flow_id', '=', $flow_info->flow_id],['id', '>', $flow_info->id]])
        return self::whereIn('flow_id',$res)->select('id')->pluck('id')->toArray();
    }

    // 经过当前用户审核入库的所有在建项目 优化后 $shiwu 事务接受模式
    public static function getprocessIdsByflow_ids($uid,$process_name_array,$shiwu_flow=0){
        if($shiwu_flow ==0){
            $flow_info =  self::whereRaw("FIND_IN_SET($uid,auto_sponsor_ids)")
                ->whereIn('process_name', $process_name_array)
                ->get(['flow_id'])->toArray();
        }else{
            //flow_id =1 申报流程
            $flow_info =  self::where('flow_id',$shiwu_flow)
                ->whereIn('process_name', $process_name_array)
                ->get(['flow_id'])->toArray();
        }
        $res=[];
        foreach ($flow_info as $key => $value) {
            $res[] = $value['flow_id'];
        }
        return self::whereIn('flow_id',$res)->select('id')->pluck('id')->toArray();
    }
    // 通过当前用户id 以及步骤名称 获取当前流程所有步骤ID
    public static function getprocessIdsAll($uid,$process_name){
        $flow_info = self::where([['auto_sponsor_ids', '=', $uid],['process_name', '=', $process_name]])->select('id','flow_id')->first();

        return self::where('flow_id', '=', $flow_info->flow_id)->select('id')->pluck('id');
    }

    // 获得角色名process_name
    public static function getRoleName($flow_process){
        return self::where(['id' => $flow_process])->value('process_name');
    }


      // 通过当前用户id，获取当前步骤所有id
    public static function getFlowProcessId($uid, $process_name){
        $arr = self::whereRaw("FIND_IN_SET($uid,auto_sponsor_ids)")->whereIn('process_name',$process_name)->get(['id'])->toArray();

        $res = [];
        foreach ($arr as $key => $value) {
            $res[] = $value['id'];
        }
        return $res;
    }

    //通过当前角色 和步骤名称 获取指定步骤ID
    public static function getprocessIdByname($uid,$process_name_now,$process_name_next){
        $flow_info = self::where([['auto_sponsor_ids', '=', $uid],['process_name', '=', $process_name_now]])->select('id','flow_id')->first();

        return self::where([['flow_id', '=', $flow_info->flow_id],['process_name', '=', $process_name_next]])->value('id');
    }


    // 获得角色名process_name
    public static function getRunFlowProcess($from_table,$from_id){
        $run_flow_process = Run::where(['from_table' => $from_table,'from_id'=>$from_id,'status'=>0])->value('run_flow_process');
        return self::where(['id'=>$run_flow_process])->value('auto_sponsor_ids');
    }

    //获取当前登录人所在流程线的项目id
    public static function getFlowId($uid,$process_name_now,$status,$from_table='complete')
    {

        $flow_id = self::whereRaw("FIND_IN_SET($uid,auto_sponsor_ids)")->whereIn('process_name',$process_name_now)->get(['flow_id'])->toArray();

        $res=[];
        foreach ($flow_id as $key => $value) {
            $res[] = $value['flow_id'];
        }
        $from_id=Run::where(['from_table'=>$from_table,'status'=>$status])->whereIn('flow_id',$res)->get(['from_id'])->toArray();
        $ress=[];
        foreach ($from_id as $v){
            $ress[]=$v['from_id'];
        }
        return $ress;
    }

    // 获得角色名process_name
    public static function getRoleNameByUid($from_table,$from_id){
        $run_flow_process = Run::where(['from_table' => $from_table,'from_id'=>$from_id,'status'=>0])->value('run_flow_process');
        $abc= self::where(['id'=>$run_flow_process])->first(['auto_sponsor_ids','process_name','auto_sponsor_text']);
        return $abc->process_name.$abc->auto_sponsor_text;
    }

    //通过当前用户id 以及步骤名称 获取步骤IDS 多角色 针对县市区 多用户 $shiwu 事务接受 传流程ID
    public static function getprocessIdByuidsOther($uid=0,$process_name_array,$shiwu_flow=0){
        if($shiwu_flow == 0){
            if($uid!=0){
                if(!empty($process_name_array)){
                    return self::whereRaw("FIND_IN_SET($uid,auto_sponsor_ids)")//多用户的情况
                    ->whereIn('process_name', $process_name_array)
                        ->select('id')->pluck('id');
                }
            }else{
                return self::whereIn('process_name', $process_name_array)->select('id')->pluck('id');
            }
        }else{
            //事务接受
            return self::whereIn('process_name', $process_name_array)
                ->where('flow_id',$shiwu_flow)
                ->select('id')->pluck('id');
        }
    }
}
