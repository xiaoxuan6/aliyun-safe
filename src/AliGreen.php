<?php

namespace James\AliGreen;
use James\AliGreen\Green;
include_once 'aliyun-php-sdk-core/Config.php';

class AliGreen
{
    private static $_instance;

    private function __clone(){
        trigger_error("clone is not allowed", E_USER_ERROR);
    }

    /**
     * Get the acs client
     * @return \DefaultAcsClient
     */
    public static function getClient(){
        date_default_timezone_set("PRC");

        $iClientProfile = \DefaultProfile::getProfile("cn-shanghai", config('aliyun.accessKeyId'), config('aliyun.accessKeySecret'));
        \DefaultProfile::addEndpoint("cn-shanghai", "cn-shanghai", "Green", "green.cn-shanghai.aliyuncs.com");
        $client = new \DefaultAcsClient($iClientProfile);

        return $client;
    }

    /**
     * create the singleton
     * @return AliGreenAPI
     */
    public static function getInstance(){

        if(empty( self::$_instance)){
            $class = get_called_class();
            self::$_instance  = new $class();
        }

        return self::$_instance;
    }

    /**
     * scene 风险场景，和传递进来的场景对应
     * suggestion 建议用户处理，取值范围：[“pass”, “review”, “block”], pass:图片正常，review：需要人工审核，block：图片违规，
     *            可以直接删除或者做限制处理
     * label 文本的分类
     * rate 浮点数，结果为该分类的概率；值越高，越趋于该分类；取值为[0.00-100.00]
     * extras map，可选，附加信息. 该值将来可能会调整，建议不要在业务上进行依赖
     *
     *  -10000  检测数据有问题
     *  10000  检测数据正常
     *  20000  检测出异常 重试三次
     * @param $request
     */
    private function processResponse($request, $title, $type){

        $client = $this->getClient();

         $response = $client->getAcsResponse($request);

        if(200 == $response->code){
            if(is_array($title))
                $taskResults = $response->data;
            else
                $taskResults = current($response->data)->results;

            if(!$taskResults)
                return $this->echoStr(-200, '请重试！');

            $arr = $this->processSceneResult($taskResults, $title, $type);

            return $this->echoStr(200, $arr);
        }else{
            return $this->echoStr(-200, '请重试！');
        }
    }

    /**
     * @param $code
     * @param $msg
     */
    private function echoStr($code, $msg){
        return array(
            'code' => $code,
            'msg' => $msg,
        );
    }

    /**
     * @param $taskResult
     */
    private function processSceneResult($taskResults, $title, $type){
        $arr = [];
        foreach ($taskResults as $value){
            if(is_array($title)){
                if($type == 'image'){
                    foreach ($value->results as $v){
                        if(in_array($v->suggestion, ['review', 'block'])){
                            $arr = [
                                'label' => $v->label,
                                'rate' => $v->rate,
                                'describe' => $v->suggestion == 'review' ? '疑似' : ($v->suggestion == 'block' ? '违规' : '正常' ),
                            ];
                        }
                        continue;
                    }
                }else{
                    $suggestion = current($value->results)->suggestion;
                    $value = current($value->results);
                    if(in_array($suggestion, ['review', 'block'])){
                        $arr = [
                            'label' => $value->label,
                            'rate' => $value->rate,
                            'describe' => $value->suggestion == 'review' ? '疑似' : ($value->suggestion == 'block' ? '违规' : '正常' ),
                        ];
                    }
                    continue;
                }
            }else{
                if(in_array($value->suggestion, ['review', 'block'])){
                    $arr = [
                        'label' => $value->label,
                        'rate' => $value->rate,
                        'describe' => $value->suggestion == 'review' ? '疑似' : ($value->suggestion == 'block' ? '违规' : '正常' ),
                    ];
                }
                continue;
            }
        }
        if(!$arr){
            $arr = [
                'rate' => $value->rate,
                'describe' => '正常'
            ];
        }

        return $arr;

    }

    /**
     * 文本垃圾检测
     * scenes字符串数组：
     *   关键词识别scene场景取值keyword
     *        分类label:正常normal 含垃圾信息spam 含广告ad 涉政politics 暴恐terrorism 色情porn 辱骂abuse
     *                  灌水flood 命中自定义customized(阿里后台自定义)
     *   垃圾检测识别场景scene取值antispam
     *        分类label:正常normal 含违规信息spam 含广告ad 涉政politics 暴恐terrorism 色情porn 违禁contraband
     *                  命中自定义customized(阿里后台自定义)
     *
     * tasks json数组 ，最多支持100个task即100段文本
     * content 待检测文本，最长4000个字符
     *
     * @param $text 支持字符串和数组
     * @return null
     */
    public function checkText($text){


        if(empty($text)){
            return null;
        }

        $request = new Green\TextScanRequest();
        $request->setMethod("POST");
        $request->setAcceptFormat("JSON");

        if(is_array($text)){

            $taskArr = [];
            foreach($text as $k => $v){
                $task = 'task'.$k;
                $$task = array('dataId' =>  md5(uniqid($task)),
                    'content' => $v,
                    'category' => 'post',
                    'time' => round(microtime(true)*1000)
                );
                array_push($taskArr, $$task);
            }
            $request->setContent(json_encode(array("tasks" => $taskArr,
                "scenes" => array("antispam"))));

        }else if(is_string($text)){
            $task1 = array('dataId' =>  md5(uniqid()),
                'content' => $text
            );
            $request->setContent(json_encode(array("tasks" => array($task1),
                "scenes" => array("antispam"))));
        }

        return $this->processResponse($request, $text, 'text');
    }

    /**
     * 图片检测
     * scenes字符串数组：
     *   图片广告识别scene场景取值ad
     *        分类label: 正常normal 含广告ad
     *   图片鉴黄识别场景scene取值porn
     *        分类label:正常normal 性感sexy 色情porn
     *   图片暴恐涉政识别场景scene取值terrorism
     *        分类label:正常normal terrorism含暴恐图片 outfit特殊装束 logo特殊标识 weapon武器 politics渉政 others	其它暴恐渉政
     *
     * tasks json数组 ，最多支持100个task即100张图片
     *
     * @param $img 支持字符串和数组
     * @return null
     */
    public function checkImg($img){

        if(empty($img)){
            return null;
        }

        $request = new Green\ImageSyncScanRequest();
        $request->setMethod("POST");
        $request->setAcceptFormat("JSON");

        if(is_array($img)){

            $taskArr = array();
            foreach($img as $k => $v){
                $task = 'task'.$k;
                $$task = array('dataId' =>  md5(uniqid($task)),
                    'url' => $v,
                    'time' => round(microtime(true)*1000)
                );
                array_push($taskArr, $$task);
            }
            $request->setContent(json_encode(array("tasks" => $taskArr,
                "scenes" => config('aliyun.scenes'))));

        }else if(is_string($img)){
            $task1 = array('dataId' =>  md5(uniqid()),
                'url' => $img,
                'time' => round(microtime(true)*1000)
            );
            $request->setContent(json_encode(array("tasks" => array($task1),
                "scenes" => config('aliyun.scenes'))));
        }

        return $this->processResponse($request, $img, 'image');
    }

}