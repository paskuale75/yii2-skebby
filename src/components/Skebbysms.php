<?php
/**
 * Created by PhpStorm.
 * User: paskuale
 * Date: 27/09/18
 * Time: 9.21
 */

namespace paskuale75\yii2skebby\src\components;

use Yii;
use yii\base\Component;
use linslin\yii2\curl;
use yii\helpers\VarDumper;
use yii\helpers\Json;

/*
 * inspired by https://github.com/YetOpen/YSkebbySms
 */


class Skebbysms extends Component
{

    const CHARSET_UTF8 = 'UTF-8';
    const CHARSET_ISO = 'ISO-8859-1';
    const TYPE_CLASSIC = 'send_sms_classic';
    const TYPE_CLASSIC_PLUS = 'send_sms_classic_plus';
    const TYPE_BASIC = 'send_sms_basic';
    const TEST_PREFIX = 'test_';
    const CREDIT_TYPE_CREDIT = 'credit_left';
    const CREDIT_TYPE_CLASSIC = 'classic_sms';
    const CREDIT_TYPE_BASIC = 'basic_sms';


    const BASEURL = 'https://api.skebby.it/API/v1.0/REST/';
    const MESSAGE_HIGH_QUALITY = 'GP';
    const MESSAGE_MEDIUM__QUALITY = 'TI';
    const MESSAGE_LOW__QUALITY = 'SI';

    const AUTH_BY_TOKEN         = 'token';
    const AUTH_BY_SESSION_KEY   = 'session';



    protected $token;
    protected $sessionKey;
    protected $userKey;
    protected $geoPrefixs = [
        'North America' => '1',
        'Italia'        => '39',
        'France'        => '33',
        'Egypt'         => '20',
        'South Africa'  => '27',
    ];


    protected $prefix;






    /** can be 'token' OR 'session' type */
    public $authenticationMethod = self::AUTH_BY_TOKEN;

    /** @var mixed Skebby action. */
    public $method = self::TYPE_CLASSIC;
    /** @var mixed Sender name. Must be max 11 chars. */
    public $sender_string = "YSkebbySms";
    /** @var mixed Sender number. Must be in international format without + sign or leading zeros. You must be allowed on
    Skebby site to send using this number */
    public $sender_number = null;
    /** @var mixed Mobile phone no to send the SMS to. It can be an array with more numbers. */
    public $to;
    /** @var string The message to be send */
    public $message;
    /** @var string Your Clickatell username */
    public $username;
    /** @var string Your Clickatell password */
    public $password;
    /** @var boolean Whether to use https */
    public $ssl = false;
    /** @var boolean Whether to use test mode (not really sending the message, just testing the gateway) */
    public $test = false;
    // component level
    /** @var boolean Whether to print debug information on screen. Useful when debugging from shell */
    public $debug = false;
    //protected $_url = "%s://gateway.skebby.it/api/send/smseasy/advanced/http.php";
    protected $_url = "%s://api.skebby.it/API/v1.0/REST/sms";

    protected $_return;



    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        if(empty($this->prefix)){
            $this->prefix = $this->geoPrefixs['Italia'];
        }
    }


    /**
     * @param mixed $userKey
     */
    public function setUserKey($userKey): void
    {
        $this->userKey = $userKey;
    }


    /**
     * Authenticates the user given it's username and password.
     * Returns the pair user_key, Session_key
     */
    private function getToken() {
        $return = false;
        $optUrl = $this::BASEURL.'token?username='.$this->username.'&password='.$this->password;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $optUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        if ($info['http_code'] != 200) {
            echo('Error! http code: ' . $info['http_code'] . ', body message: ' . $response);
        }
        else {
            $values = explode(";", $response);
            //echo('user_key: ' . $values[0]);
            //echo('Access_token: ' . $values[1]);
            $this->setUserKey($values[0]);
            $this->token = $values[1];
        }
    }

    private function getSessionKey(){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this::BASEURL.'login?username='.$this->username.'&password='.$this->password);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        if ($info['http_code'] != 200) {
            echo('Error! http code: ' . $info['http_code'] . ', body message: ' . $response);
        }
        else {
            $values = explode(";", $response);
            //echo('user_key: ' . $values[0]);
            //echo('Session_key: ' . $values[1]);
            $this->setUserKey($values[0]);
            $this->sessionKey = $values[1];
        }
    }

    /**
     *
     * @param string money “true” or “false”  add user current money to response
     * @param string typeAliases “true” or “false”  Returns the actual names for the message types
     * instead of the internal ID. This is not done by default only because of retrocompatibility issues.
     *
     * @return json object representing the user status
     */

    function getUserStatus($money=false, $typeAliases=false)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::BASEURL.'status?getMoney='.$money.'&typeAliases='.$typeAliases);
        $curlHttpParams = [
            'Content-type: application/json'
        ];

        if($this->authenticationMethod == self::AUTH_BY_TOKEN){
            $this->getToken();
            $element = 'Access_token:'. $this->token;
            array_push($curlHttpParams, $element);
        }else{
            $this->getSessionKey();
            $element = 'Session_key:'. $this->sessionKey;
            array_push($curlHttpParams, $element);
        }

        if($money){
            $element = 'getMoney: true';
            array_push($curlHttpParams, $element);
        }

        $userKeyElement = 'user_key: '.$this->userKey;
        array_push($curlHttpParams, $userKeyElement);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHttpParams);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        if ($info['http_code'] != 200) {
            echo('Error! http code: ' . $info['http_code'] . ', body message: ' . $response);
        } else {

            $obj = json_decode($response);
            VarDumper::dump($obj,10,true);
        }

    }

    /**
     * params:
     * @to is array : A list of recipents phone numbers ex.(+3922334455, +3955667788, ...)
     * @message string
     * @messageType (high=>'GP', medium=>'TI' , low=>'SI')
     * @sender (optional) : the sender name
     */
    function send($to, $message, $messageType=false, $sender=false)
    {
        $options = [
            'returnCredits'     => true,
            'returnRemaining'   => true,
            'recipient'         => self::sanifyNumber($to),
            'scheduled_delivery_time'   => '',
            'message'       => $message,
            'message_type'  => ($messageType)?$messageType:self::MESSAGE_MEDIUM__QUALITY,
            'sender'        => ($sender)?$sender:$this->sender_string,
        ];

        $payload = Json::encode($options);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::BASEURL.'sms');
        $curlHttpParams = [
            'Content-type: application/json'
        ];

        if($this->authenticationMethod == self::AUTH_BY_TOKEN){
            $this->getToken();
            $element = 'Access_token:'. $this->token;
            array_push($curlHttpParams, $element);
        }else{
            $this->getSessionKey();
            $element = 'Session_key:'. $this->sessionKey;
            array_push($curlHttpParams, $element);
        }

        $userKeyElement = 'user_key: '.$this->userKey;

        array_push($curlHttpParams, $userKeyElement);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHttpParams);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);


        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        $obj = json_decode($response);

        return $obj;
    }


    private function sanifyNumber($numbers){
        $ret = false;
        foreach($numbers as $number){
            $ret[] = '+'.$this->prefix.$number;
        }
        return $ret;
    }



    /**
     * Sends an request to Clickatell HTTP API. Error messages are logged.
     * @param string $method The method used. Common mehtods are send, ping and auth. For more check the Clickatell docs.
     * @param array $params Key=>Value array for POST data. Values must be URL-encoded.
     * @return mixed The returned information (without "OK: " status) or false if it fails.
     */
    protected function skebbyRequest($params) {
        if ($this->debug === true) {
            Yii::debug('Skebby SMS request start: '.implode("|",$params), 'ext.YSkebbySms'); //FIXME
        }

        $curl = new curl\Curl();
        $curlParams = [
            'CURLOPT_CONNECTTIMEOUT'    => 10,
            'CURLOPT_RETURNTRANSFER'    => true,
            'CURLOPT_TIMEOUT'           => 60,
            'CURLOPT_USERAGENT'         => 'Generic Client',
            'CURLOPT_POST'              => count($params),
            'CURLOPT_POSTFIELDS'        => http_build_query($params)
        ];

        if ($this->ssl === true) {
            $curlParams['CURLOPT_URL'] = sprintf($this->_url,"https");
            $curlParams['CURLOPT_SSL_VERIFYHOST'] = 2;
            //curl_setopt($request,CURLOPT_URL,sprintf($this->_url,"https"));
            //curl_setopt($request,CURLOPT_SSL_VERIFYHOST, 2);
        } else {

            $curlParams['CURLOPT_URL'] = sprintf($this->_url,"http");
            //curl_setopt($request,CURLOPT_URL,sprintf($this->_url,"http"));
        }


        $response = $curl->setGetParams($curlParams)->get($curlParams['CURLOPT_URL']);


        //$response = curl_exec($request);
        if ($this->debug === true) {
            Yii::warning('Skebby SMS reply: '.$response, 'ext.YSkebbySms');
            VarDumper::dump($response);
        }
        // if the request fails
        if ($response === false) {
            Yii::warning('Skebby SMS request failed: unknown network error', 'ext.YSkebbySms');
            $this->_return ['message'] = Yii::t('ext.YSkebbySms','Unknown network error');
            $this->_return ['result'] = false;
            return;
        }
        parse_str($response,$this->_return);
        if ($this->_return ['result'] !== "OK") {
            $this->_return ['result'] = false;
        } else {
            $this->_return ['result'] = true;
        }

        return;
    }
}