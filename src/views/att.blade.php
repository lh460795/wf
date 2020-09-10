@include('Work::pub.base')
<link rel="stylesheet" type="text/css" href="/vendor/workflow/work/workflow.3.0.css"/>
<link rel="stylesheet" type="text/css" href="/vendor/workflow/work/multiselect2side.css"/>
<form  class="form-horizontal" action="{{url('/wf/save_attribute')}}" method="post" name="form" id="form">
    <style type="text/css">
        #box{
            height: auto;
            width: auto;
            border: 1px solid #ccc;

        }
        ul{
            height: 30px;
            width: 600px;
            padding: 0;
            margin: 0;
        }
        li{
            display: block;
            text-align: center;
            width: 80px;
            float: left;
            list-style: none;
            cursor: pointer;
            height: 30px;
            line-height: 30px;
        }
        .choice{
            background: #409EFF;
            color: #fff;
        }
        .tab-item{
            display: none;
        }
        .show{
            display: block;
        }
    </style>
    </head>
    <body>
    <ul>
        <li tabid="1" class="choice">节点信息</li>
        <li tabid="2">节点属性</li>
        <li tabid="3">节点人员</li>
        <li tabid="4">节点转出</li>
        <li tabid="5">节点事务</li>
    </ul>
    <div id="box">
        <div class="tab-item show">
            <input type="hidden" name="flow_id" value="{{$one->flow_id}}"/>
            <input type="hidden" name="process_id" value="{{$one->id}}"/>
            <table class="tables">
                <tr><td>节点ID</td><td>{{$one->id}}</td></tr>
                <tr><td>步骤名称</td><td><input type="text" class="smalls" name="process_name" value="{{$one->process_name}}"></td></tr>
                <tr><td>步骤尺寸</td><td><input type="text" class="smalls" name="style_width" value="{{$one->style['width']}}" style='width:60px'> X <input type="text" class="smalls" name="style_height" value="{{$one->style['height']}}" readonly style='width:60px'></td></tr>
            </table>

        </div>
        <div class="tab-item">
            <table class="tables">
                <tr><td>步骤类型</td><td><input type="radio" name="process_type" value="is_step" @if($one->process_type == 'is_step') checked="checked" @endif>正常步骤
                        <input type="radio" name="process_type" value="is_one" @if($one->process_type == 'is_one') checked="checked" @endif>第一步</td></tr>
                <tr><td>调用方法</td><td><input type="text" class="smalls" name="wf_action"  value="{{$one->wf_action ?? 'view'}}"></td></tr>
                <tr><td>会签方式</td><td><select name="is_sing" >
                            <option value="1" @if($one->is_sing == 1) selected="selected" @endif>允许会签</option>
                            <option value="2" @if($one->is_sing == 2) selected="selected" @endif>禁止会签</option>
                        </select></td></tr>
                <tr><td>回退方式</td><td><select name="is_back" >
                            <option value="1" @if($one->is_back == 1) selected="selected" @endif>允许回退</option>
                            <option value="2" @if($one->is_back == 2) selected="selected" @endif>不允许</option>
                        </select></td></tr>

            </table></div>
        <div class="tab-item"> <table class="tables">
                <tr><td>办理人员</td><td colspan='3'><select name="auto_person" id="auto_person_id" datatype="*" nullmsg="请选择办理人员或者角色！">
                            <option value="">请选择</option>
                            @if($one->process_type != 'is_one')<option value="3" @if($one->auto_person == 3) selected="selected" @endif>自由选择</option>@endif
                            <option value="4" @if($one->auto_person == 4) selected="selected" @endif>指定人员</option>
                            <option value="5" @if($one->auto_person == 5) selected="selected" @endif>指定角色</option>
                            <option value="6" @if($one->auto_person == 6) selected="selected" @endif>事务接受</option>
                        </select></td></tr>
                <tr id="auto_person_3" @if($one->auto_person != 3) class="hide" @endif><td>自由选择</br>
                    <a class="btn" onclick="layer_show('办理人','{{url('/wf/super_user',['kid'=>'range_user'])}}','350','500')">选择</a>
                </td><td>
                    <input type="hidden" name="range_user_ids" id="range_user_ids" value="{{$one->range_user_ids}}">
                    <input class="input-xlarge" readonly="readonly" type="hidden" placeholder="选择办理人范围" name="range_user_text" id="range_user_text" value="{{$one->range_user_text ?? ''}}">

                    <span id='range_user_html'>
                    @if(count(explode(",",$one->range_user_text)) >= 1)
					<table class='tables'><tr><td>序号</td><td>名称</td></tr>
                        @foreach (explode(",",$one->range_user_text) as $key=>$vo)
							<tr><td>{{$key}}</td><td>{{$vo}}</td></tr>
                        @endforeach
						</table>@else
						<h4>Tip:请按右侧选择添加办理人员</h4>
                        @endif
					</span>

                </td>
                </tr>
                <tr id="auto_person_4" @if($one->auto_person != 4) class="hide"@endif><td>指定人员
                    </br/><a class="btn" onclick="layer_show('办理人','{{url('/wf/super_user',['kid'=>'auto_sponsor'])}}','350','500')">选择</a></td><td>

                    <input type="hidden" name="auto_sponsor_ids" id="auto_sponsor_ids" value="{{$one->auto_sponsor_ids}}">
                    <input class="input-xlarge" readonly="readonly" type="hidden" placeholder="指定办理人" name="auto_sponsor_text" id="auto_sponsor_text" value="{{$one->auto_sponsor_text ?? ''}}">
                    <span id='auto_sponsor_html'>
                    @if(count(explode(",",$one->auto_sponsor_text)) >= 1)
					<table class='tables'><tr><td>序号</td><td>名称</td></tr>
                        @foreach (explode(",",$one->auto_sponsor_text) as $key=>$vo)
                            <tr><td>{{$key}}</td><td>{{$vo}}</td></tr>
                        @endforeach
						</table>	@else
						<h4>Tip:请按右侧选择添加办理人员</h4>
                    @endif
					</span>
                </td>
                </tr>
                <tr id="auto_person_5" @if($one->auto_person != 5) class="hide" @endif><td>指定角色<br/><a class="btn" onclick="layer_show('办理人','{{url('/wf/super_role')}}','350','500')">选择</a></td><td>
                    <input type="hidden" name="auto_role_ids" id="auto_role_value" value="{{$one->auto_role_ids}}" >

                    <span id='auto_role_html'>
                        @if(count(explode(",",$one->auto_role_text)) >= 1)
                            <table class='tables'><tr><td>序号</td><td>名称</td></tr>
                        @foreach (explode(",",$one->auto_role_text) as $key=>$vo)
                                    <tr><td>{{$key}}</td><td>{{$vo}}</td></tr>
                                @endforeach
						</table>	@else
                            <h4>Tip:请按右侧选择添加办理人员</h4>
                        @endif
			        </span>

                    <input class="input-xlarge" readonly="readonly" type="hidden" placeholder="指定角色" name="auto_role_text" id="auto_role_text" value="{{$one->auto_role_text ?? ''}}">
                </td>
                </tr>
                <tr id="auto_person_6" @if($one->auto_person != 6) class="hide" @endif><td>事务接受</td><td>
                    取业务表<select class="smalls" name='work_text'>
                        <option value="">选择字段</option>
                            @foreach ($from as $key=>$v)
                        <option value="{{$key}}" @if($key == $one["work_text"]) selected="selected" @endif>{{$v}}</option>
                            @endforeach
                    </select>的
                    <select name="work_ids"  nullmsg="人员">

                        <option value="1"  @if($one["work_ids"] == 1) selected="selected" @endif>制单人员</option>
                    </select>
                </td>
                </tr>



            </table>
        </div>
        <div class="tab-item">
            <table class="tables">
                <tr><td>步骤模式</td><td  colspan='3'>
                        <select name="wf_mode" id="wf_mode_id" datatype="*" nullmsg="请选择步骤模式">
                            <option value="">请选择步骤模式</option>
                            @if (count($one->process_to)>1)
                            <option value="1" @if($one->wf_mode == 1) selected="selected" @endif>转出模式（符合执行）</option>
                            <option value="2" @if($one->wf_mode == 2) selected="selected" @endif>同步模式（均需办理）</option>
                            @else
                            <option value="0" @if($one->wf_mode == 0) selected="selected" @endif>单线模式（流程为直线型单一办理模式）</option>
                            @endif
                        </select>
                    </td></tr>
                <!--重新设计，带转出模式-->
                <tr id='wf_mode_2' @if($one->wf_mode != 1) class="hide" @endif>
                <td colspan=4>
                    <table class="table" ><thead><tr><th style="width:30px;">步骤</th><th>转出条件设置</th></tr></thead><tbody>
                        <!--模板-->
                        @foreach ($process_to_list as $item)
                            @if(in_array($item->id,$one->process_to))
                        <tr>
                            <td style="width: 30px;">{{$item->process_name}}{{$item->id}}</td>
                            <td>
                                <table class="table table-condensed">
                                    <tbody>
                                    <tr>
                                        <td>
                                            <select id="field_{{$item->id}}" class="smalls">
                                                <option value="">选择字段</option>
                                                @foreach($from as $key=>$v)
                                                    <option value="{{$key}}">{{$v}}</option>
                                                @endforeach
                                            </select>
                                            <select id="condition_{{$item->id}}" class="smalls" style="width: 60px;">
                                                <option value="=">=</option>
                                                <option value="&lt;&gt;"><></option>
                                                <option value="&gt;">></option>
                                                <option value="&lt;"><</option>
                                                <option value="&gt;=">>=</option>
                                                <option value="&lt;="><=</option>
                                                <option value="include">含</option>
                                                <option value="exclude">不含</option>
                                            </select>
                                            <input type="text" id="item_value_{{$item->id}}" class="smalls" style="width: 40px;">
                                            <select id="relation_{{$item->id}}" class="smalls" style="width: 40px;"><option value="AND">AND</option><option value="OR">OR</option>
                                            </select>
                                        </td>
                                        <td>
                                            <button type="button" class="wf_btn" onclick="fnAddLeftParenthesis('{{$item->id}}')">（</button>
                                            <button type="button" class="wf_btn" onclick="fnAddRightParenthesis('{{$item->id}}')">）</button>
                                            <button type="button" onclick="fnAddConditions('{{$item->id}}')" class="wf_btn">新增</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <select id="conList_{{$item->id}}" multiple="" style="width: 100%;height: 80px;">
                                                {!! $item->condition !!}
                                            </select>
                                        </td>
                                        <td>
                                            <button type="button" onclick="fnDelCon('{{$item->id}}')" class="wf_btn">删行</button>
                                            <button type="button" onclick="fnClearCon('{{$item->id}}')" class="wf_btn">清空</button>
                                            <input name="process_in_set_{{$item->id}}" id="process_in_set_{{$item->id}}" type="hidden">
                                        </td>
                                    </tr>

                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </table></td></tr></table>
        </div>

        <div class="tab-item">
            <table class="tables">
                <tr><td width='160px'style="display:table-cell; vertical-align:middle">事务SQL
                        <hr>
                        单据ID：@from_id<br/>
                        节点ID：@run_id<br/>
                        提交意见：@check_con

                    </td><td><textarea name='work_sql'  type="text/plain" style="width:100%;height:100px;">{{$one->work_sql ?? ''}}</textarea>
                        Tip:UPDATE Table SET field1=value1 WHERE id=@run_id;
                    </td></tr>
                <tr><td style="display:table-cell; vertical-align:middle">事务MSG
                        <hr>
                        单据ID：@from_id<br/>
                        节点ID：@run_id<br/>
                        提交意见：@check_con
                    </td><td><textarea name='work_msg'  type="text/plain" style="width:100%;height:100px;">{{$one->work_msg ?? ''}}</textarea>
                        Tip:您好,您有需要审批的业务,业务编号为：@run_id;
                    </td></tr>
            </table>

        </div>


    </div>
    <table class="tables">
        <tr>
            <td style='text-align: center;'>
                <a onclick="layer_close()" class="btn" >取消</a>
                <button  class="btn btn-primary radius" type="submit"><i class="Hui-iconfont">&#xe632;</i> 保存</button>
            </td></tr>
    </table>

    <script type="text/javascript">
        $("li").click(function(){
            $(this).attr("class","choice")
            $(this).siblings().attr("class","")
            var itemId = $(this).attr("tabid")-1;

            $("#box").find("div:eq("+itemId+")").attr("class","show")
            $("#box").find("div:eq("+itemId+")").siblings().attr("class","tab-item")
        })
    </script>



    <input type="hidden" name="process_condition" id="process_condition" value='{{$one->process_tos}}'>

    <div>


    </div>
</form>
<script type="text/javascript" src="/vendor/workflow/work/jquery-1.7.2.min.js?"></script>
<script type="text/javascript" src="/vendor/workflow/work/jquery-ui-1.9.2-min.js?" ></script>
<script type="text/javascript" src="/vendor/workflow/work/multiselect2side.js?" ></script>
<script type="text/javascript" src="/vendor/workflow/work/workflow-att.3.0.js"></script>
<script type="text/javascript" src="/vendor/workflow/lib/Validform/5.3.2/Validform.min.js"></script>
<script type="text/javascript">
    $(function(){
        $("#form").Validform({
            tiptype:1,
            ajaxPost:true,
            showAllError:true,
            callback:function(ret){
                ajax_progress(ret);
            }
        });
    });
    var wf_mode = "{{$one->wf_mode ?? '0'}}";
    if(wf_mode ==1){
        check_from();
    }
</script>
