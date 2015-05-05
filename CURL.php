<?php
namespace backend\modules\upload\models;

/**
 * Класс для отправки запросов на сервер
 * Class CURL
 */
class CURL{
    protected $_url; //адресс
    protected $_post; // boolean
    protected $_postFields; //параметры

    /**
     * Инициализация параметров
     * @param string $url - адресс
     * @param string $postFields - параметры
     */
    public function __construct($url, $postFields){
        $this->_url = $url;
        $this->_postFields = $postFields;
    }

    /**
     * Метод GET
     */
    public function get(){
        $this->setUrl( $this->_postFields);
        return $this->createCurl();
    }

    /**
     * Метод POST
     */
    public function post(){
        $this->setPost();
        return $this->createCurl();
    }

    /**
     * Метод для отправки запрос на сервер
     * @return mixed
     */
    protected function createCurl(){

        $ch = curl_init();
        $url = $this->_url;

        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_HEADER, true);

        if($this->_post) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_postFields);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: multipart/form-data"));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_VERBOSE , 0 );


        $filename = NULL;
        $fp = NULL;
        $uploadFileName = NULL;
        $uploadFileType = NULL;
        $code = NULL;



        if (($response = curl_exec($ch)) !== false)
        {
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == '200')
            {
                $reDispo = '/^Content-Disposition: .*?filename=(?<f>[^\s]+|\x22[^\x22]+\x22)\x3B?.*$/m';
                if (preg_match($reDispo, $response, $mDispo))
                {
                    $filename = trim($mDispo['f'],' ";');

                    $fp = fopen(\Yii::$app->basePath.'/files/'.$filename, "w+");
                    curl_setopt($ch, CURLOPT_FILE, $fp);
                    fwrite($fp, $response);
                }
            }
            list($uploadFileName, $uploadFileType) = explode('.', $filename);
        }
        curl_close($ch);
        fclose($fp);


        return array('code' => $code, 'name' => $uploadFileName, 'type' => $uploadFileType);

    }

    /**
     * Установка Post запроса
     */
    protected function setPost(){
        $this->_post = true;
    }

    /**
     * Назначем новый урл
     * @param $postFields
     */
    protected function setUrl($postFields){
        $this->_url = $this->_url."?".$postFields;
    }

}