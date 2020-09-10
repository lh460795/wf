@include('Work::pub.base')
<nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i> 工作流插件
<a onclick="layer_show('添加工作流','{{url('/wf/wfadd')}}','550','400')" class="btn btn-primary radius">添加工作流</a>
</nav>
<div class="page-container">

<table class="table table-border table-bordered table-bg">
    <tr>
        <th>ID</th>
        <th>流程名称</th>
        <th>流程类型</th>
        <th>添加时间</th>
		<th>状态</th>
        <th>操作</th>
    </tr>
    @foreach ($list as $vo)
    <tr>
        <td>{{$vo->id}}</td>
        <td><span title="{{$vo->flow_desc}}">{{$vo->flow_name}}</span></td>
        <td>{{$type[$vo->type]}}</td>
        <td>{{date('Y-m-d H:i:s',$vo->add_time)}}</td>
		 <td>@if($vo->status == 0) 正常 @else 禁用 @endif</td>
        <td>
		{{-- @if(!$vo->edit) --}}
            <a class='btn  radius size-S' onclick="layer_show('修改','{{url('/wf/wfedit',['id'=>$vo->id])}}','550','400')"> 修改</a>
                <a class='btn  radius size-S' data-title="设计" href="{{url('/wf/wfdesc',['flow_id'=>$vo->id])}}" target="_blank"> 设计流程</a>
                {{--<a class='btn  radius size-S' data-title="设计" data-href="{{url('/wf/wfdesc',['flow_id'=>$vo->id])}}" onclick="Hui_admin_tab(this)" > 设计流程</a>--}}
        {{-- @else --}}
            @if($vo->edit)
            <a class='btn  radius size-S'> 运行中....</a>
            @endif
        {{-- @endif --}}
@if($vo->status == 0)
    <a class='btn  radius size-S' href="{{url('/wf/wfchange',['id'=>$vo->id,'status'=>1])}}" target= > 禁用</a>
@else
    <a class='btn  radius size-S' href="{{url('/wf/wfchange',['id'=>$vo['id'],'status'=>0])}}" target= > 启用</a>
@endif


</td>
</tr>
@endforeach
</table>

</div>
