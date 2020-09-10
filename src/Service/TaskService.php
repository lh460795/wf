<?php
/**
*+------------------
* Tpflow 工作流任务服务
*+------------------
* Copyright (c) 2006~2018 http://cojz8.cn All rights reserved.
*+------------------
* Author: guoguo(1838188896@qq.com)
*+------------------
*/

namespace Lh\Workflow\Service;

use Lh\Workflow\Service\Task\TaskFlow;
use Lh\Workflow\Service\Task\BackFlow;
use Lh\Workflow\Service\Task\SingFlow;
use Lh\Workflow\Service\Task\SupFlow;

class TaskService{
	/**
	 * 普通流程通过
	 *
	 * @param  $config 参数信息
	 * @param  $uid  用户ID
	 */
	public static function doTask($config,$uid){
		return TaskFlow::doTask($config,$uid);
	}
	/**
	 * 流程退回
	 *
	 * @param  $config 参数信息
	 * @param  $uid  用户ID
	 */
	public static function doBack($config,$uid){
		return BackFlow::doTask($config,$uid);
	}
	/**
	 * 会签操作
	 *
	 * @param  $config 参数信息
	 * @param  $uid  用户ID
	 */
	public static function doSing($config,$uid){
		return SingFlow::doTask($config,$uid);
	}

	/**
	 * 普通流程通过
	 *
	 * @param  $config 参数信息
	 * @param  $uid  用户ID
	 */
	public static function doSingEnt($config,$uid,$wf_actionid){
		return SingFlow::doSingEnt($config,$uid,$wf_actionid);
	}
	/**
	 * 实例超级接口
	 *
	 * @param  $wfid 工作流ID run_id
	 * @param  $uid  用户ID
	 */
	public static function doSupEnd($wfid,$uid){
		return SupFlow::doSupEnd($wfid,$uid);
	}
}
