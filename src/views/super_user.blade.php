@include('Work::pub.base')
<link rel="stylesheet" type="text/css" href="/vendor/workflow/work/multiselect2side.css" media="screen" />
<script type="text/javascript" src="/vendor/workflow/work/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="/vendor/workflow/work/multiselect2side.js" ></script>
<article class="page-container">
<table class="table table-bordered table-bg">
			<tr><td>
	<form method="post">
		<div class="text-l">
			<input type="text" id="key" style="width:150px" class="input-text">
			<a id="search" class="btn btn-success size-S">搜人员</a>
			</div>
		</form>
			</td></tr>
			<tr><td>
			 <select name="dialog_searchable" id="dialog_searchable" multiple="multiple" style="display:none;">
           <!--  <option value="all">【全部】</option> -->
           @foreach ($user as $item)
           <option value="{{$item->id}}" >{{$item->username}}({{$item->name}})</option>
          @endforeach
        </select>
			</td></tr>
			<tr><td>
			<button class="btn btn-info" type="button" onclick='call_back()' id="dialog_confirm">确定</button>
			<button class="btn" type="button" id="dialog_close">取消</button>
			</td></tr>
			</table>
</article>
<script type="text/javascript">
	function call_back(){
			var nameText = [];
            var idText = [];
            var html = "<table class='tables'><tr><td>序号</td><td>名称</td></tr>";
            if(!$('#dialog_searchable').val())
            {
               layer.msg('未选择');
				return false;
            }else
            {
              $('#dialog_searchable option').each(function(){
                if($(this).attr("selected"))
                {
                    if($(this).val()=='all')//有全部，其它就不要了
                    {
                        nameText = [];
                        idText = [];
                        nameText.push($(this).text());
                        idText.push($(this).val());
                        return false;
                    }
                    nameText.push($(this).text());
                    idText.push($(this).val());
                }
                });
                for (x in nameText){
                    html += '<tr><td>'+x+'</td><td>';
                    html += nameText[x];
                    html += '</td></tr>';
                }
                html += '</table>';
                var name = nameText.join(',');
				var ids = idText.join(',');
            }
		var index = parent.layer.getFrameIndex(window.name);
		parent.layer.msg('设置成功');
		parent.$('#{{$kid}}_ids').val(ids);
        var name_new = name.replace(/\(.*?\)/g,'');//移除字符串中的所有()括号（包括其内容）
		parent.$('#{{$kid}}_text').val(name_new);
        parent.$('#{{$kid}}_html').html(html);
		parent.layer.close(index);
	}
    $(function(){
          $('#dialog_searchable').multiselect2side({
            selectedPosition: 'right',
            moveOptions: false,
            labelsx: '备选',
            labeldx: '已选',
            autoSort: true
            //,autoSortAvailable: true
        });
        //搜索用户
        $("#search").on("click",function(){
			var url = "{{url('/wf/super_get')}}";
			$.post(url,{"type":'user',"key":$('#key').val()},function(data){
				layer.msg(data.msg);
				var userdata = data.data;
				var optionList = [];
            for(var i=0;i<userdata.length;i++){
                optionList.push('<option value="');
                optionList.push(userdata[i].value);
                optionList.push('">');
                optionList.push(userdata[i].text);
                optionList.push('(');
                optionList.push(userdata[i].username);
                optionList.push(')');
                optionList.push('</option>');
            }
            $('#dialog_searchablems2side__sx').html(optionList.join(''));
			},'json');
        });
        $("#dialog_close").on("click",function(){
			var index = parent.layer.getFrameIndex(window.name);
            parent.layer.close(index);
        });
    });

</script>
