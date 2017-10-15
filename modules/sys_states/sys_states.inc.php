<?php
$details=array();
$red_state=0;
$yellow_state=0;
if($this->object_title=='System') {
	$cycles=json_decode(gg('System.redstate'), TRUE);
	if($cycles!=null) {
	foreach($cycles as $k=>$v) {
	 $tm=getGlobal('ThisComputer.'.$k.'Run');
	 if (time()-$tm>5*60) {
	  $red_state=1;
	  $details[]=$v." ".LANG_GENERAL_STOPPED.".";
	 }
	}}

	$cycles=json_decode(gg('System.yellowstate'), TRUE);
	if($cycles!=null) {
	foreach($cycles as $k=>$v) {
	 $tm=getGlobal('ThisComputer.'.$k.'Run');
	 if (time()-$tm>10*60) {
	  $yellow_state=1;
	  $details[]=$v." ".LANG_GENERAL_STOPPED.".";  
	 }
	}}
} elseif($this->object_title=='Communication') {
	$cycles=json_decode(gg('Communication.redstate'), TRUE);
	if($cycles!=null) {
	foreach($cycles as $k=>$v) {
		if (!isOnline($k)) {
		 $red_state=1;
		 $details[]=$v;
		}
	}}
	$cycles=json_decode(gg('Communication.yellowstate'), TRUE);
	if($cycles!=null) {
	foreach($cycles as $k=>$v) {
		if (!isOnline($k)) {
		 $yellow_state=1;
		 $details[]=$v;
		}
	}}
}

if ($red_state) {
 $state='red';
 $state_title=LANG_GENERAL_RED; 
} elseif ($yellow_state) {
 $state='yellow';
 $state_title=LANG_GENERAL_YELLOW;  
} else {
 $state='green';
 $state_title=LANG_GENERAL_GREEN;   
}

$new_details=implode(". ",$details);
if ($this->getProperty("stateDetails")!=$new_details) {
 $this->setProperty('stateDetails',$new_details);
}

if ($this->getProperty('stateColor')!=$state) {
 $this->setProperty('stateColor',$state);
 $this->setProperty('stateTitle',$state_title);
 if ($state!='green') {
  say($this->getProperty('title')." ".LANG_GENERAL_CHANGED_TO." ".$state_title.".");
  say(implode(". ",$details));
 } else {
  say($this->getProperty('title')." ".LANG_GENERAL_RESTORED_TO." ".$state_title);
 }
 $this->callMethod('stateChanged');
}