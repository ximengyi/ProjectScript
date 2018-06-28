<?php
// 兼容windows/ 切换目录
define("ROOTDIR",__DIR__);
$tpl = 'new_project_tpl';

$workdir = str_replace('\\','/',__DIR__);
// 替换\为/拷贝写入文件时使用
define('WORKDIR',$workdir);


//echo $development_config;die;
//创建目录
function make_dir($dirname){
    if(!file_exists($dirname)){
        if(mkdir($dirname,0755,false)){
          echo '已创建'.$dirname.'目录···'."\n";
        }else{
            exit('目录创建失败，用sudo 尝试'."\n");
        }
    }else{

        echo "已存在{$dirname}目录，跳过创建步骤···\n";
    }
}

// 正则匹配替换文件文本
function find_and_rep($source_file,$target_file,$pattern,$replacement)
{
    $current = file_get_contents($source_file);
    $current =  preg_replace($pattern,$replacement,$current,2);     
    file_put_contents($target_file,$current);
    echo '已成功写入'.$target_file."\n";
}
 //修改tob_uid
function modify_tob_uid($source_config,$new_config,$env){

 fwrite(STDOUT, "{$env}tob_uid: "); 
 $rep_uid = trim(fgets(STDIN));
 $tob_uid_pattern = '(\[\'tob_uid\'\] *= *[0-9]+)';
 $replacement = '[\'tob_uid\'] = '.$rep_uid;
 find_and_rep($source_config,$new_config,$tob_uid_pattern,$replacement);
}
//替换namespace
function replace_namespace($project_name,$class_path){

    $namespace_pattern = '/\\\aon\\\/';
    $replacement = '\\'.$project_name.'\\';
    find_and_rep($class_path,$class_path,$namespace_pattern,$replacement);
   }

function copy_all_file_rep_namespace($source_dir,$target_dir,$project_name)
{
    copy_all_file($source_dir,$target_dir);
    $file_array = get_all_file($target_dir);
   //遍历替换congtroller namespace文件夹
    foreach ($file_array as $key => $value) {
        $class_path = $target_dir.'/'.$value;
        replace_namespace($project_name,$class_path);
        $class_path ='';
    }   
   }

function copy_all_file($source_dir,$target_dir)
{ 
    
    if(!is_dir($source_dir)||!is_dir($target_dir)){

        echo '拷贝目标必须为文件夹';
        return false ;
    }
    $dir_array = scandir($source_dir);
    chdir($source_dir);
    foreach ($dir_array as $key => $value) {
        if($value=='.' || $value=='..'){
            continue;
        }
        if(is_dir($value)){
                echo "已跳过".$value."目录\n";
        }else{
              $tmp_dir =  $target_dir.'/'.$value;
 
              copy($value,$tmp_dir);
              $tmp_dir ='';
        }        
    }
    chdir(ROOTDIR);
} 

function get_all_file($now_dir){

    $dir_array = scandir($now_dir);
    $file_array = [];
    foreach($dir_array as $key=>$value){
        if($value=='.' || $value=='..' || is_dir($value)){
            continue;
        }

      $file_array[] = $value;
    }

    return $file_array;

}
//初始化路径

//模板文件名


if(!file_exists($tpl))
{
    exit('未发现模板，请将解压缩的模板项目放入当前目录中！！');
}

fwrite(STDOUT, "请输入项目名称: "); 
$project_name = trim(fgets(STDIN));
 
echo "您输入了{$project_name}"."\n";  

//--------------------------------

//初始化文件夹路径
$source_config_dir = WORKDIR.'/'.$tpl.'/'.'config';
$target_config_dir = WORKDIR.'/'.$project_name.'/'.'config';

$source_controller_dir = WORKDIR.'/'.$tpl.'/'.'controllers';
$target_controller_dir = WORKDIR.'/'.$project_name.'/'.'controllers';

//初始化替换文件路径
$development_config = WORKDIR . '/'.$tpl.'/config/development/config.php';
$new_development_config = WORKDIR .'/'.$project_name.'/config/development/config.php';

$production_config = WORKDIR . '/'.$tpl.'/config/production/config.php';
$new_production_config = WORKDIR .'/'.$project_name.'/config/production/config.php';

$testing2_config = WORKDIR . '/'.$tpl.'/config/testing2/config.php';
$new_testing2_config = WORKDIR .'/'.$project_name.'/config/testing2/config.php';
    
//----------------------------------------------
//创建根目录
make_dir($project_name);
//创建主目录
if (chdir($project_name)) {  
    make_dir('config');
    make_dir('controllers');
    make_dir('core');
    make_dir('logics');
 }else{
     exit('切换目录失败');
 }

 //生成config文件夹
//=================================================== 
 if(chdir('config')){
    make_dir('development');
    make_dir('production');
    make_dir('testing2');
    chdir(ROOTDIR);
    echo  getcwd()."\n";

//  fwrite(STDOUT, "请输入开发环境tob_uid: "); 
//  $rep_uid = trim(fgets(STDIN));
 
//  $tob_uid_pattern = '(\[\'tob_uid\'\] *= *81)';
//  $replacement = '[\'tob_uid\'] = '.$rep_uid;
//  find_and_rep($development_config,$new_development_config,$tob_uid_pattern,$replacement);

 modify_tob_uid($development_config,$new_development_config,'开发环境');
 modify_tob_uid($production_config,$new_production_config,'生产环境');
 modify_tob_uid($testing2_config,$new_testing2_config,'测试环境');

 //拷贝文件
 copy_all_file($source_config_dir,$target_config_dir);
 }
//==========================================================

 //拷贝controller 文件夹
//  copy_all_file($source_controller_dir,$target_controller_dir);
//  $file_array = get_all_file($target_controller_dir);
// //遍历替换congtroller namespace文件夹
//  foreach ($file_array as $key => $value) {
//      $class_path = $target_controller_dir.'/'.$value;
//      replace_namespace($project_name,$class_path);
//      $class_path ='';
//  }
 //echo getcwd();
 copy_all_file_rep_namespace($source_controller_dir,$target_controller_dir,$project_name);

//============================================================



//==========================================================
/**
 * 创建core目录
 */

//创建source目录
$source_core_dir = WORKDIR.'/'.$tpl.'/'.'core'.'/';
$new_core_dir =WORKDIR.'/'.$project_name.'/'.'core'.'/';
$source_core_source_dir = WORKDIR.'/'.$tpl.'/'.'core'.'/'.'source';
$new_core_source_dir = WORKDIR.'/'.$project_name.'/'.'core'.'/'.'source';
make_dir($new_core_source_dir);
copy_all_file_rep_namespace($source_core_source_dir,$new_core_source_dir,$project_name);

//创建core 目录下_project_controller 替换aontroller namespace 
$source_core_controller_file = $source_core_dir.'aon_Controller.php';
$new_core_controller_file = $new_core_dir.$project_name.'_Controller.php';
$aon_Controller_pattern = '/\\\aon\\\/';
$aon_Controller_replacement = '\\'.$project_name.'\\';
find_and_rep($source_core_controller_file,$new_core_controller_file,$aon_Controller_pattern,$aon_Controller_replacement);


//替换aon_controller
$aon_Controller_pattern = '/aon_Controller/';
$aon_Controller_replacement =  $project_name.'_Controller';
find_and_rep($new_core_controller_file,$new_core_controller_file,$aon_Controller_pattern,$aon_Controller_replacement);

//============================================================

/**
 * 创建logics
 */
$source_logic_dir = WORKDIR.'/'.$tpl.'/'.'logics'.'/';
$new_logic_dir =WORKDIR.'/'.$project_name.'/'.'logics'.'/';
copy_all_file_rep_namespace($source_logic_dir,$new_logic_dir,$project_name);


//==========================================================================


//copy index。php 等剩余文件
$tpl_dir = WORKDIR.'/'.$tpl;
$new_dir = WORKDIR.'/'.$project_name;
copy_all_file($tpl_dir,$new_dir);

//=====================================================================================

//修改.gitlab-ci.yml 文件
//======================================================================================
$yml_file_path = $new_dir.'/'.'.gitlab-ci.yml';
$yml_pattern = '/aon/';
$yml_replacement = $project_name;
find_and_rep($yml_file_path,$yml_file_path,$yml_pattern,$yml_replacement);
//========================================================================================
//================替换中文名称
// mb_internal_encoding("UTF-8");
// mb_regex_encoding("UTF-8");

// $yml_pattern = '/新项目模版/';
// fwrite(STDOUT, "请输入项目中文名称: "); 
// $cn_name = trim(fgets(STDIN));
// $yml_replacement = $cn_name;

// find_and_rep($yml_file_path,$yml_file_path,$yml_pattern,$yml_replacement);

