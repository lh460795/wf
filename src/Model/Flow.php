<?php
namespace Lh\Workflow\Model;

use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;


class Flow extends Model{
    use Filterable;

    protected $table = 'wf_flow';
    protected $guarded = [];
    protected $fields_all;

    // 获取分管领导
    public static function getFlowName($flow_id){
    	$fen_name = self::where(['id' => $flow_id])->value('flow_name');
    	if($fen_name == "县市区" || $fen_name == "三区"){
    	    $fen_name = "";
        }
        return $fen_name;
    }

    public function project(){
        return $this->hasMany('App\Models\Project','wf_id','id');
    }

}
