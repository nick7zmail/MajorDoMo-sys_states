<?php
/**
* РЎС‚Р°С‚СѓСЃС‹ СЃРёСЃС‚РµРјС‹ 
* @package project
* @author Wizard <sergejey@gmail.com>
* @copyright http://majordomo.smartliving.ru/ (c)
* @version 0.1 (wizard, 20:10:09 [Oct 13, 2017])
*/
//
//
class sys_states extends module {
/**
* sys_states
*
* Module class constructor
*
* @access private
*/
function sys_states() {
  $this->name="sys_states";
  $this->title="Статусы системы";
  $this->module_category="<#LANG_SECTION_SYSTEM#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data=0) {
 $p=array();
 if (IsSet($this->id)) {
  $p["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $p["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $p["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
 }
 return parent::saveParams($p);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $tab;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
}
/**
* Run
*
* Description
*
* @access public
*/
function run() {
 global $session;
  $out=array();
  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }
  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  $out['TAB']=$this->tab;
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}
/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {
	$objects=getObjectsByClass("systemStates");
	$i=0;
	foreach($objects as $obj) {
	if(gg($obj['TITLE'].'.tab_title')!='') $out['TITLES'][$i]['TITLE']=gg($obj['TITLE'].'.tab_title'); else $out['TITLES'][$i]['TITLE']=$obj['TITLE'];
	 $out['TITLES'][$i]['TAB']=$obj['TITLE'];
	 if($this->tab==$obj['TITLE'])  $out['TITLES'][$i]['ACTIVE']=1;
	 $i++;
	}
	if($this->tab) {
		 $out['OBJECT']=$this->tab;
		 $out['TAB_TITLE']=gg($this->tab.'.tab_title');
		 $out['STATE_TITLE']=gg($this->tab.'.title');
		if($this->tab=='System') {
		$qry="1 AND TITLE LIKE 'cycle%Run'";
		$res=SQLSelect("SELECT properties.* FROM properties WHERE $qry ORDER BY TITLE");
		$total=count($res);
		for($i=0;$i<$total;$i++) {
		  $title = $res[$i]['TITLE'];
		  $title = preg_replace('/Run$/', '', $title);
		  $out['CYCLES'][$i]['CYCLE']=$title;
		  $json_cycles_yellow=json_decode(gg('System.yellowstate'), TRUE);
		  $json_cycles_red=json_decode(gg('System.redstate'), TRUE);
		  if($json_cycles_yellow!=null) {
		  foreach($json_cycles_yellow as $key=>$val) {
			  if($key==$title)	{
				  $out['CYCLES'][$i]['CYCLE_NAME']=$val;
				  $out['CYCLES'][$i]['STATE']='yellow';
			  }
		  }}
		  if($json_cycles_red!=null) {
		  foreach($json_cycles_red as $key=>$val) {
			  if($key==$title)	{
				  $out['CYCLES'][$i]['CYCLE_NAME']=$val;
				  $out['CYCLES'][$i]['STATE']='red';
			  }
		  }}
		}
		} elseif ($this->tab=='Communication') {
		$res=SQLSelect("SELECT * FROM pinghosts");
		$total=count($res);
		for($i=0;$i<$total;$i++) {
		  $title=$res[$i]['TITLE'];
		  $out['CYCLES'][$i]['CYCLE']=$title;
		  $json_cycles_yellow=json_decode(gg('Communication.yellowstate'), TRUE);
		  $json_cycles_red=json_decode(gg('Communication.redstate'), TRUE);
		  if($json_cycles_yellow!=null) {
		  foreach($json_cycles_yellow as $key=>$val) {
			  if($key==$title)	{
				  $out['CYCLES'][$i]['CYCLE_NAME']=$val;
				  $out['CYCLES'][$i]['STATE']='yellow';
			  }
		  }}
		  if($json_cycles_red!=null) {
		  foreach($json_cycles_red as $key=>$val) {
			  if($key==$title)	{
				  $out['CYCLES'][$i]['CYCLE_NAME']=$val;
				  $out['CYCLES'][$i]['STATE']='red';
			  }
		  }}
		}
		}	 	
	}
	if($this->mode=='save_settings') {
		global $tab_title;
		global $state_title;
		sg($this->tab.'.tab_title', $tab_title);
		sg($this->tab.'.title', $state_title);
		$this->redirect('?'.'&tab='.$this->tab);
	}
	if($this->mode=='save_params') {
		if($this->tab=='System') {
			$qry="1 AND TITLE LIKE 'cycle%Run'";
			$res=SQLSelect("SELECT properties.* FROM properties WHERE $qry ORDER BY TITLE");
			$total=count($res);
			for($i=0;$i<$total;$i++) {
				$title = $res[$i]['TITLE'];
				$title = preg_replace('/Run$/', '', $title);				
				global ${'target_state_'.$title};
				if(${'target_state_'.$title}=='yellow') {
					global ${'cycle_name_'.$title};
					$yellow_arr[$title]=${'cycle_name_'.$title};
				}
				if(${'target_state_'.$title}=='red') {
					global ${'cycle_name_'.$title};
					$red_arr[$title]=${'cycle_name_'.$title};
				}
			}
			$yellow_arr=json_encode($yellow_arr, JSON_UNESCAPED_UNICODE);
			sg('System.yellowstate',$yellow_arr);
			$red_arr=json_encode($red_arr, JSON_UNESCAPED_UNICODE);
			sg('System.redstate',$red_arr);
		} elseif($this->tab=='Communication') {
			$res=SQLSelect("SELECT * FROM pinghosts");
			$total=count($res);
			for($i=0;$i<$total;$i++) {
				$title = $res[$i]['TITLE'];
				global ${'target_state_'.$title};
				if(${'target_state_'.$title}=='yellow') {
					global ${'cycle_name_'.$title};
					$yellow_arr[$title]=${'cycle_name_'.$title};
				}
				if(${'target_state_'.$title}=='red') {
					global ${'cycle_name_'.$title};
					$red_arr[$title]=${'cycle_name_'.$title};
				}
			}
			$yellow_arr=json_encode($yellow_arr, JSON_UNESCAPED_UNICODE);
			sg('Communication.yellowstate',$yellow_arr);
			$red_arr=json_encode($red_arr, JSON_UNESCAPED_UNICODE);
			sg('Communication.redstate',$red_arr);
		}
	}
	
}
/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {
 $this->admin($out);
}
/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($data='') {
  parent::install();
  addClassMethod('systemStates', 'checkState', 'require(DIR_MODULES.\'sys_states/sys_states.inc.php\');');
  addClassProperty('systemStates', 'title');
  addClassProperty('systemStates', 'tab_title');
  addClassProperty('systemStates', 'stateTitle');
  addClassProperty('systemStates', 'yellowstate');
  addClassProperty('systemStates', 'redstate');
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgT2N0IDEzLCAyMDE3IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
