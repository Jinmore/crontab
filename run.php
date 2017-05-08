<?php
/**
* PHP crontab 
* ʹ�÷���:��config.ini������Ҫִ�еļƻ�����
*          ��php-cliִ��run.php
* @author Devil
**/	
while(true){
		$config = parse_ini_file('config.ini',true);
		foreach($config as $cronName=>$info){
			$runStatus = timeMark($info['run_time']);
			if($runStatus){
				echo '['.date('Y-m-d H:i:S').']Task:['.$cronName."]->Is Runing\r\n";
				$handle = popen('cd '.$info['cd_dir'].'&'.$info['common'],'r');
				while(!feof($handle)) {
					$buffer = fgets($handle);
					writeLog($info['log_dir'],$buffer);
				}
				pclose($handle);
			}
		}
	sleep(1);
}

	
/**
*����ʱ��ƻ��������
*/
//$match = '*/3 18 * * *';
//$res = timeMark($match);	
function timeMark($match){
	$s = date('s');//��
	$i = date('i');//��
	$h = date('H');//ʱ
	$d = date('d');//��
	$m = date('m');//��
	$w = date('w');//��
	$run_time = explode(' ',$match);
	$data[] = T($run_time[0],$s,'s');
	$data[] = T($run_time[1],$i,'i');
	$data[] = T($run_time[2],$h,'h');
	$data[] = T($run_time[3],$d,'d');
	$data[] = T($run_time[4],$m,'m');
	$data[] = T($run_time[5],$w,'w');
	return !in_array(false,$data)?true:false;
}

//��������ʱ�����ϸ��
function T($rule,$time,$timeType){
	if(is_numeric($rule)){
		return $rule == $time ?true:false;
	}elseif(strstr($rule,',')){
		$iArr = explode(',',$rule);
		return in_array($time,$iArr)?true:false;
	}elseif(strstr($rule,'/') && !strstr($rule,'-')){
		list($left,$right) = explode('/',$rule);
		return in_array($left,array('*',0)) && analysis_t($time,$right)?true:false;
	}elseif(strstr($rule,'/') && strstr($rule,'-')){
		list($left,$right) = explode('/',$rule);
		if(strstr($left,'-')){
			return analysis($left,$right,$time,$timeType);
		}
	}elseif(strstr($rule,'-')){
		list($left,$right) = explode('-',$rule);
		return $time >= $left && $time <=$right?true:false;
	}elseif($rule =='*' || $rule==0){
		return true;
	}else{
		return false;
	}
}
//����12-2 23-22 �κ�ʱ���ͨ��
//$rank��Χ  $num��ֵ $time��ǰʱ�� $timeTypeʱ������
function analysis($rank,$num,$time,$timeType){
	$type = array(
		'i'=>59,'h'=>23,'d'=>31,'m'=>12,'w'=>6,
	);
	list($left,$right) = explode('-',$rank);
	if($left<$right){
		for($i=$left;$i<=$right;$i=$i+$num){
			$temp[] = $i;
		}
	}
	if($left > $right){
		for($i=$left;$i<=$type[$timeType]+$right;$i=$i+$num){
			$temp[] = $i>$type[$timeType]?$i-$type[$timeType]:$i;
		}
	}
	return in_array($time,$temp)?true:false;
}
//���ݵ�ǰʱ������Ƿ�ʱѭ��ִ��
//$time��ǰʱ�� $num����ֵ 
function analysis_t($time,$num){
	return $time%$num == 0?true:false;
}

function writeLog($path,$body){
	$temp = pathinfo($path);
	if(!file_exists($temp['dirname'])){
		mkdir($temp['dirname'],0755,true);
	}
	file_put_contents($path,'['.date('Y-m-d H:i:s')."]\r\n".$body."\r\n\n",FILE_APPEND);
}

