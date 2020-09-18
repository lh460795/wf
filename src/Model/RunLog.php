<?php
namespace Lh\Workflow\Model;

use Illuminate\Database\Eloquent\Model;
use App\Work\Model\FlowProcess;
use App\Work\Model\RunProcess;
use App\Models\User;

class RunLog extends Model{
    protected $table = 'wf_run_log';
    protected $guarded = [];

    /**
     * 审核意见
     * @param $form_id 主键id
     * @param $from_table 关联的表名
     * @param $table_type 审核日志状态
     **/
    public static function log($from_id,$from_table,$table_type=''){
        $where = function($query)use($from_id,$from_table,$table_type){
            if($from_table == 'adjust'){
                //调整终止审核记录多加一个条件
                return $query->where([
                    'from_id'       =>$from_id,
                    'from_table'    =>$from_table,
                    'table_type'    =>2,
                ]);
            } elseif ($from_table == 'complete'){
                //出库 未完结 审核记录多加一个条件
                //判断是否传过来了 $table_type  来查询对应的日志或者所有日志
                if (!$table_type){
                    return $query->where([
                        'from_id'       =>$from_id,
                        'from_table'    =>$from_table,
                    ]);
                }else{
                    return $query->where([
                        'from_id'       =>$from_id,
                        'from_table'    =>$from_table,
                        'table_type'    =>$table_type,
                    ]);
                }

            }else {
                return $query->where([
                    'from_id'       =>$from_id,
                    'from_table'    =>$from_table,
                ]);
            }
        };
        // default系统默认审核通过的，不用展示
        $log = self::where($where)->where('btn', '<>', 'default')
            ->orderBy('dateline', 'asc')
            ->get(['id','uid','content','btn','created_at','run_process','flow_process','table_type'])->toArray();

        if($log){
            foreach ($log as $key => $value) {
                if($value['flow_process'] == 0)
                {
                    $log[$key]['content']='';
                    //该记录用户角色名
                    $log[$key]['rolename'] = '立项单位';
                    //该记录用户名
                    $log[$key]['username'] = User::getUserName($value['uid']);

                    $log[$key]['btn'] = self::getBtn($value['btn'], $log[$key]['rolename']);
                }else{
                    //该记录用户角色名
                    $log[$key]['rolename'] = FlowProcess::getRoleName($value['flow_process']);
                    //该记录用户名
//                    $log[$key]['username'] = RunProcess::getUserName($value['run_process']);
                    $log[$key]['username'] = User::getUserName($value['uid']);

                    $log[$key]['btn'] = self::getBtn($value['btn'], $log[$key]['rolename']);

                    $log[$key]['content'] = $value['content'] ?? '';
                }
            }
            foreach ($log as $k=>$v)
            {
                if(in_array($v['rolename'],['科室','副秘书长','"五化"办','五化办人员','五化办主任']))
                {
                    if(stristr($v['btn'],'退回')){

                    }else{
                        $log[$k]['content']='';
                    }
                }
            }
        }

        return $log;
    }

    //返回操作日志 通过 退回 送审
    protected static function getBtn($btn,$role){
        $ok = '通过';
        if($role =='"五化"办' || $role == "五化办"){
            $ok = '送审';
        }else{
            $ok = '通过';
        }
        $type = ['Send'=>'申报','ok'=>$ok,'Back'=>'退回','SupEnd'=>'终止流程',
            'Sing'=>'会签提交','sok'=>'会签同意','SingBack'=>'会签退回','SingSing'=>'会签再会签'];

        return $type[$btn] ?? '';
    }

    //通过uid得到退回的项目id
    public static function getBhPid($uid,$from_table)
    {
        $arr = self::where([['btn', '=', 'Back'],['from_table', '=', $from_table],['uid', '=', $uid]])->get(['from_id'])->toArray();
        $res = [];
        foreach ($arr as $key => $value) {
            $res[] = $value['from_id'];
        }
        return $res;
    }
    //立项单位通过uid得到退回的项目id
    public static function getBhPids($uid,$from_table)
    {
        $runid = self::where([['btn', '=', 'Back'],['from_table', '=', $from_table]])->groupBy('from_id')->get(['run_id'])->toArray();
        if(empty($runid))
        {
            return $runid;
        }
        $res = [];
        foreach ($runid as $key => $value) {
            $res[] = $value['run_id'];
        }
        $from_id=Run::where(['from_table'=>$from_table])->whereIn('uid',$uid)->whereIn('id',$res)->get(['from_id'])->toArray();
        $ress=[];
        foreach ($from_id as $v)
        {
            $ress[]=$v['from_id'];
        }
        return $ress;
    }

    //通过项目id得到退回人
    public static function getUidById($from_id,$from_table='complete')
    {
         $flow_process = self::where(['from_table'=>$from_table,'from_id'=>$from_id,'btn'=>'Back'])->orderBy('dateline','desc')->select('flow_process')->first();
         $tname=FlowProcess::where(['id'=>$flow_process['flow_process']])->value('auto_sponsor_text');
         return $tname;
    }
}
