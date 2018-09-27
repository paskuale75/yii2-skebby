<?php
/**
 * Created by PhpStorm.
 * User: paskuale
 * Date: 27/09/18
 * Time: 9.21
 */

namespace paskuale75\yii2_skebby\components;

use Yii;
use yii\base\Component;


/*
 * inspired by https://github.com/YetOpen/YSkebbySms
 */


class SmsSkebby extends Component{

    const CHARSET_UTF8 = 'UTF-8';
    const CHARSET_ISO = 'ISO-8859-1';
    const TYPE_CLASSIC = 'send_sms_classic';
    const TYPE_CLASSIC_PLUS = 'send_sms_classic_plus';
    const TYPE_BASIC = 'send_sms_basic';
    const TEST_PREFIX = 'test_';
    const CREDIT_TYPE_CREDIT = "credit_left";
    const CREDIT_TYPE_CLASSIC = "classic_sms";
    const CREDIT_TYPE_BASIC = "basic_sms";


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
    protected $_url = "%s://gateway.skebby.it/api/send/smseasy/advanced/http.php";
    protected $_return;


    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
    }

}