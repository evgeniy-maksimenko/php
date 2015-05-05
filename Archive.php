<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 18.04.15
 * Time: 14:33
 */

namespace backend\modules\upload\models;


class Archive {

    protected $_password;
    protected $file;

    function __construct($password, $file){
        $this->_password = $password;
        $this->file = $file;
    }

    public function compression(){
        $zip = new \ZipArchive();

        $zip->open(\Yii::$app->basePath.'/files/zip/'.$this->file.".zip", 1);
        shell_exec("zip -j --password ".$this->_password." files/".$this->file.".zip ".\Yii::$app->basePath."/files/".$this->file);
        $zip->close();
    }
}