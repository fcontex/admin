<?php
return array
(
  //上传文件大小限制（MB）
  'uploadSize'	=> 20,
  //上传文件类型
  'uploadSuffix' => array
  (
    'image' => 'jpg,jpeg,png,gif,webp',
    'file'	=> 'txt,log,md,csv,pdf,doc,docx,xls,xlsx,ppt,pptx,apk',
    'archive'	=> 'zip,rar,tar,7z,gz,tgz',
    'video'	=> 'mp4,webm,mov',
    'audio'	=> 'mp3,wav,m4a,ogg,flac'
  ),
  //附件目录（相对 store，物理路径 = PATH_STORE.uploadDir.'/'）
  'uploadDir'	=> 'upload',
  
  //Cookie配置
  'cookie_domain' => '',     //作用域
  'cookie_path'   => '/',    //作用路径
  'cookie_pre'    => 'fc_',  //前缀
  'cookie_ttl'    => 0,      //默认生命周期，0 表示随浏览器进程
  
  //系统管理员，逗号分隔
  'system_user'	=> '1',

  //基准皮肤（失效时由 default 兜底）
  'skin_default'	=> 'default'
);
?>