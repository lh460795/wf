<?php
/**
 * 后台工作流控制器
 */
namespace Lh\Workflow\Controllers;


use Illuminate\Config\Repository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Lh\Workflow\Workflow;


class WorkController
{
    protected $config;
    /**
     * 构造方法
     */
    public function __construct(Repository $config)
    {
        $this->config = $config->get('workflow');
    }

    public function test($str){
        dd($str);
        return '测试自定义扩展包';
    }

    /**
     * 流程设计首页
     * @param $map 查询参数
     */
    public function wfindex(Request $request)
    {
        $list = Workflow::FlowApi('List');
        $type = ['project' => '申报项目', 'adjust' => '调整项目', 'complete' => '完结项目', 'un_complete' => '未完成项目', 'null' => '尚未选择', '' => '尚未选择'];
        return view('Work::index', ['list' => $list, 'type' => $type]);
    }

    /**
     * 工作流设计界面
     */
    public function wfdesc(Request $request, $flow_id)
    {
        if ($flow_id <= 0) {
            return response()->json('参数有误，请返回重试!');
        }
        $one = Workflow::FlowApi('GetFlowInfo', $flow_id);
        if (!$one) {
            return response()->json('未找到数据，请返回重试!');
        }
        return view('Work::desc', ['one' => $one, 'process_data' => Workflow::ProcessApi('All', $flow_id)]);
    }

    /**
     * 流程添加
     */
    public function wfadd(Request $request)
    {
        $platform = $request->input('platform') ?? 'pc';
        $data = $request->all();
        if (count($data) == 0) {
            return view('Work::add', ['type' => ['project' => '申报项目', 'adjust' => '调整项目', 'complete' => '完结项目', 'un_complete' => '未完成项目']]);
        }
        //$data['uid'] = session('uid');
        $data['add_time'] = time();
        $ret = Workflow::FlowApi('AddFlow', $data);
        if ($ret['code'] == 0) {
            //操作日志写死
            activitys('backend')
                ->withProperties(['type' => '工作流管理','table'=>'wf_flow','id'=>$ret['data'],'data'=>$data])
                ->log('admin 通过'.$platform.'登录后台系统:添加工作流', '工作流管理');
            return $this->msg_return('发布成功！');
        } else {
            return $this->msg_return($ret['data'], 1);
        }
    }

    /**
     * 流程修改
     */
    public function wfedit(Request $request, $id)
    {
        $platform = $request->input('platform') ?? 'pc';
        if ($request->method() == 'POST') {
            $data = $request->all();
            $ret = Workflow::FlowApi('EditFlow', $data);
            if ($ret['code'] == 0) {
                //操作日志写死
                activitys('backend')
                    ->withProperties(['type' => '工作流管理','table'=>'wf_flow','id'=>$ret['data'],'data'=>$data])
                    ->log('admin 通过'.$platform.'登录后台系统:修改工作流', '工作流管理');
                return $this->msg_return('修改成功！');
            } else {
                return $this->msg_return($ret['data'], 1);
            }
        }
        $data = [];
        $data['id'] = $id;
        if ($id) {
            $data['info'] = Workflow::FlowApi('GetFlowInfo', $id);
        }
        $data['type'] = ['project' => '申报项目', 'adjust' => '调整项目', 'complete' => '完结项目', 'un_complete' => '未完成项目'];
        return view('Work::add', $data);
    }

    /**
     * 状态改变
     */
    public function wfchange(Request $request, $id = 0, $status = 0)
    {
        $platform = $request->input('platform') ?? 'pc';
        $data = ['id' => $id, 'status' => $status];
        $ret = Workflow::FlowApi('EditFlow', $data);
        if ($ret['code'] == 0) {
            //操作日志写死
            activitys('backend')
                ->withProperties(['type' => '工作流管理','table'=>'wf_flow','id'=>$ret['data'],'data'=>$data])
                ->log('admin 通过'.$platform.'登录后台系统:工作流状态修改', '工作流管理');
//            return response()->json('操作成功');
            return redirect()->back();
        } else {
            return redirect()->back();
        }
    }

    /**
     * 删除流程
     **/
    public function delete_process(Request $request)
    {
        return response()->json(Workflow::ProcessApi('ProcessDel', $request->get('flow_id'), $request->get('process_id')));
    }

    public function del_allprocess(Request $request)
    {
        return response()->json(Workflow::ProcessApi('ProcessDelAll', $request->get('flow_id')));
    }

    /**
     * 添加流程
     **/
    public function add_process(Request $request)
    {
        $flow_id = $request->get('flow_id', 0);
        $one = Workflow::FlowApi('GetFlowInfo', $flow_id);
        if (!$one) {
            return response()->json(['status' => 0, 'msg' => '添加失败,未找到流程', 'info' => '']);
        }
        return response()->json(Workflow::ProcessApi('ProcessAdd', $flow_id));
    }

    /**
     * 保存布局
     **/
    public function save_canvas(Request $request)
    {
        return response()->json(Workflow::ProcessApi('ProcessLink', $request->get('flow_id'), $request->get('process_info')));
    }

    //右键属性
    public function wfatt(Request $request)
    {
        $info = Workflow::ProcessApi('ProcessAttView', $request->get('id', 0));
        //dump($info);
        $data['op'] = $info['show'];
        $data['one'] = $info['info'];
        $data['from'] = $info['from'];
        $data['process_to_list'] = $info['process_to_list'];
        $data['child_flow_list'] = $info['child_flow_list'];
        //dump($data);
        return view('Work::att', $data);
    }

    public function save_attribute(Request $request)
    {
        $data = $request->all();
        return response()->json(Workflow::ProcessApi('ProcessAttSave', $data['process_id'], $data));
    }

    //用户选择控件
    public function super_user(Request $request, $type = '')
    {
        $data['user'] = DB::table('users')->select('id', 'real_name as username','username as name')->get()->toArray();
        //dd($data['user']);
        $data['kid'] = $type;
        return view('Work::super_user', $data);
    }

    //用户选择控件
    public function super_role(Request $request)
    {
        $data['role'] = DB::table('roles')->select('id', 'name as username')->where('type',2)->get();
        return view('Work::super_role', $data);
    }

    public function super_get(Request $request)
    {
        $type = trim($request->get('type'));
        if ($type == 'user') {
            $info = DB::table('users')->select('id as value', 'real_name as text','username')->where('real_name', 'like', '%' . $request->get('key') . '%')->get()->toArray();
            //dd($info);
//            foreach ($info as $key=>$val){
//                $info[$key]['text'] = $val['text'].'('.$val['username'].')';
//            }
        } else {
            $info = DB::table('roles')->select('id as value', 'name as text')->where('name', 'like', '%' . $request->get('key') . '%')->where('type',2)->get();
        }
        return response()->json(['data' => $info, 'code' => 1, 'msg' => '查询成功！']);
    }

    /*流程监控*/
    public function wfjk($map = [])
    {
        $data['list'] = Workflow::worklist();
//        foreach ($data['list'] as $key=>$val){
//            if(in_array($val->from_id,[Project::DJ_1,Project::DJ_2,Project::DJ_3])){
//                unset($data['list'][$key]); //排除冻结项目
//            }
//        }
        return view('Work::wfjk', $data);
    }

    public function btn($wf_fid, $wf_type, $status)
    {
        $url = url("/wf/wfcheck/", ["wf_type" => $wf_type, "wf_title" => '2', 'wf_fid' => $wf_fid]);
        $url_star = url("/wf/wfstart/", ["wf_type" => $wf_type, "wf_title" => '2', 'wf_fid' => $wf_fid]);
        switch ($status) {
            case 0:
                return '<span class="btn  radius size-S" onclick=layer_show(\'发起工作流\',"' . $url_star . '","450","350")>发起工作流</span>';
                break;
            case 1:
                $st = 0;
                $user_name ='';
                $flowinfo = Workflow::workflowInfo($wf_fid, $wf_type, ['uid' => session('uid'), 'role' => session('role')]);

                if ($flowinfo != -1) {
                    if(!isset($flowinfo['status'])){
                        return '<span class="btn btn-danger  radius size-S" onclick=javascript:alert("提示：当前流程故障，请联系管理员重置流程！")>Info:Flow Err</span>';
                    }
                    if ($flowinfo['sing_st'] == 0) {
                        $user_name =$flowinfo['status']['sponsor_text'];
                        $user = explode(",", $flowinfo['status']['sponsor_ids']);

                        if ($flowinfo['status']['auto_person'] == 3 || $flowinfo['status']['auto_person'] == 4||$flowinfo['status']['auto_person']==6) {
                            if (in_array(session('uid'), $user)) {
                                $st = 1;
                            }
                        }
                        if ($flowinfo['status']['auto_person'] == 5) {
                            if (in_array(session('role'), $user)) {
                                $st = 1;
                            }
                        }
                    } else {
                        if ($flowinfo['sing_info']['uid'] == session('uid')) {
                            $st = 1;
                        }else{
                            $user_name =$flowinfo['sing_info']['uid'];
                        }
                    }
                } else {
                    return '<span class="btn  radius size-S">无权限</span>';
                }
                if ($st == 1) {
                    return '<span class="btn  radius size-S" onclick=layer_show(\'审核\',"' . $url . '","850","650")>审核('.$user_name.')</span>';
                } else {
                    return '<span class="btn  radius size-S">无权限('.$user_name.')</span>';
                }

            case 100:
                echo '<span class="btn btn-primary" onclick=layer_show(\'代审\',"' . $url . '?sup=1","850","650")>代审</span>';
                break;

                break;
            default:
                return '';
        }
    }

    protected static function status($status)
    {
        switch ($status) {
            case 0:
                return '<span class="label radius">保存中</span>';
                break;
            case 1:
                return '<span class="label radius" >流程中</span>';
                break;
            case 2:
                return '<span class="label label-success radius" >审核通过</span>';
                break;
            default: //-1
                return '<span class="label label-danger radius" >退回修改</span>';
        }
    }

    protected function msg_return($msg = "操作成功！", $code = 0, $data = [], $redirect = 'parent', $alert = '', $close = false, $url = '')
    {
        $ret = ["code" => $code, "msg" => $msg, "data" => $data];
        $extend['opt'] = [
            'alert' => $alert,
            'close' => $close,
            'redirect' => $redirect,
            'url' => $url,
        ];
        $ret = array_merge($ret, $extend);
        return response()->json($ret);
    }

    /*发起流程，选择工作流*/
    public function wfstart(Request $request)
    {
        $info = ['wf_type' => $request->get('wf_type'), 'wf_title' => $request->get('wf_title'), 'wf_fid' => $request->get('wf_fid')];
        $flow = Workflow::getWorkFlow($request->get('wf_type'));
        $data['flow'] = $flow;
        $data['info'] = $info;
        return view('Work::wfstart', $data);
    }

    /*正式发起工作流*/
    public function start_save(Request $request)
    {
        $data = $request->all();
        $flow = Workflow::startworkflow($data, session('uid'));
        if ($flow['code'] == 1) {
            return $this->msg_return('Success!');
        }
    }

    public function wfcheck(Request $request)
    {
        $info = ['wf_title' => $request->get('wf_title'), 'wf_fid' => $request->get('wf_fid'), 'wf_type' => $request->get('wf_type')];
        $info = json_decode(json_encode($info, true));
        $data['info'] = $info;
        $data['flowinfo'] = Workflow::workflowInfo($request->get('wf_fid'), $request->get('wf_type'), ['uid' => session('uid'), 'role' => session('role')]);
        $data['flowinfo'] = json_decode(json_encode($data['flowinfo'], true));
        return view('Work::check', $data);
    }

    public function do_check_save(Request $request)
    {
        $data = $request->all();
        $flowinfo = Workflow::workdoaction($data, session('uid'));
        if($flowinfo['code']=='0'){
            return $this->msg_return('Success!');
        }else{
            return $this->msg_return($flowinfo['msg'],1);
        }
    }

    public function ajax_back(Request $request)
    {
        $flowinfo = Workflow::getprocessinfo($request->get('back_id'), $request->get('run_id'));
        return response()->json($flowinfo);
    }

    public function Checkflow(Request $request)
    {
        $fid = $request->get('fid', 0);
        return Workflow::SuperApi('CheckFlow', $fid);
    }

    public function wfup(Request $request)
    {
        return view('Work::wfup');
    }

    public function wfend(Request $request)
    {
        //后期要修改
        Workflow::SuperApi('WfEnd', $request->get('id'), session('uid',1));
        return $this->msg_return('Success!');
    }

    public function wfupsave(Request $request)
    {
        $files = $request->file('file');
        $insert = [];
        foreach ($files as $file) {
            $path = public_path() . '/uploads/';
            $info = $file->move($path);
            if ($info) {
                $data[] = $info->getSaveName();
            } else {
                $error[] = $file->getError();
            }
        }
        return $this->msg_return($data, 0, $info->getInfo('name'));
    }

}
