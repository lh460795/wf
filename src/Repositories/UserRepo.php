<?php
namespace Lh\Workflow\Repositories;

use Lh\Workflow\Model\User;
use Illuminate\Support\Facades\DB;

class UserRepo{
    /**
	 * 获取用户信息
	 *
	 * @param $wf_type
	 */
	public static function getUser()
	{
        $user_table  =  [
            'user'=>['user','id','username','id as id,username as username','username'],
            'role'=>['role','id','name','id as id,name as username','name']
        ];
		return  User::select('id as id','username as username')->get();
	}
	public static function getRole()
	{
		return  Role::select('id as id ','name as username')->get();
	}
	public static function ajaxGet($type,$keyword){
		if($type=='user'){
			$map['username']  = array('like','%'.$keyword.'%');
			return User::select('id as id','username as username')->where($map)->get();
        }else{
			$map['name']  = array('like','%'.$keyword.'%');
			return Role::select('id as id','name as username')->where($map)->get();
        }
    }

    public static function GetUserInfo($id)
    {
        //用户自定义表
        $user_table  =  [
            'users'=>['users','id','username','id as id,username as username','username'],
            'role'=>['role','id','name','id as id,name as username','name']
        ];

        return  Db::table('users')
            ->where('id',$id)
            ->selectRaw('id as id,username as username')
            ->first();
    }
}
