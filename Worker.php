<?php
namespace backend\modules\upload\models;

use Yii;
use common\models\Tranc;
/**
 * Метод для обработки файлов
 * Class Worker
 */
class Worker {

    protected $_filePath; // Полный путь к файлу
    protected $_ttsVoice; // Голос, который используется для синтеза текста
    protected $_text; //Текст для синтеза
    protected $_postFields;
    protected $_fileId;

    /**
     * @param $filePath
     */
    public function __construct($filePath, $fileId){
        $this->_filePath = $filePath;
        $this->_fileId = $fileId;
    }

    /**
     *
     */
    public function combine($userId){
        $filePath = $this->_filePath;
        $content = file_get_contents($filePath);
        $this->setText(urlencode($content));
        $this->setVoice(urlencode(self::TTS_VOICE));
        $this->setPostFields();
        return $this->response($userId, $this->sendFile($content));
    }

    /**
     * @param $content
     * @return mixed
     */
    protected function sendFile($content){
        $cURL = new CURL(self::URL, $this->_postFields);
        $length = mb_strlen($content);
        if($length <= self::TEXT_SIZE) {
            $response = $cURL->get();
        } else {
            $response = $cURL->post();
        }
        return $response;
    }

    /**
     * @param array $data
     * @return array|null
     */
    protected function response($userId, array $data){
        $status = NULL;
        if($data['code'] == 200) {
            $model = Files::findOne($this->_fileId);
            $model->receive_file_name = $data["name"];
            $model->receive_file_type = "wav";
            $model->receive_file_path = Yii::$app->basePath.'/files/';
            $model->updated_at = date("Y-m-d H:i:s");
            $model->save();


        if($_SERVER['SERVER_NAME']=='advanced.loc')
        {
            shell_exec("/usr/local/bin/sox -w -s -L -r 8000 -c 1 ".Yii::$app->basePath.'/files/'.$data["name"].".".$data["type"]." ".Yii::$app->basePath.'/files/'.$data["name"].".wav");
        } else{
            shell_exec("sox -t raw -b 16 -e signed-integer -r 8000 -B -c1 ".Yii::$app->basePath.'/files/'.$data["name"].".".$data["type"]." ".Yii::$app->basePath.'/files/'.$data["name"].".wav");
        }
            $status = array("status"=>200,"file"=>$data["name"].".wav");
        } else {
            $status = array("status"=>400,"desc"=>"wrong response");
        }
        return $status;
    }

    /**
     * @param $text
     */
    protected function setText($text){
        $this->_text = $text;
    }

    /**
     * @param $ttsVoice
     */
    protected function setVoice($ttsVoice){
        $this->_ttsVoice = $ttsVoice;
    }

    /**
     * Собираем тело запроса
     */
    protected function setPostFields(){
        $postFeds  = "apikey=".self::API_KEY;
        $postFeds .= "&id=".self::ID;
        $postFeds .= "&ttsVoice=".$this->_ttsVoice;
        $postFeds .= "&textFormat=".self::TEXT_FORMAT;
        $postFeds .= "&text=".$this->_text;
        $this->_postFields = $postFeds;
    }

}