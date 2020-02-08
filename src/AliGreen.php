<?php

namespace James\AliGreen;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
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
    public static function getClient()
    {
        date_default_timezone_set("PRC");

        $iClientProfile = \DefaultProfile::getProfile(config('aliyun.region'), config('aliyun.accessKeyId'), config('aliyun.accessKeySecret'));
        \DefaultProfile::addEndpoint(config('aliyun.region'), config('aliyun.region'), "Green", "green.".config('aliyun.region').".aliyuncs.com");
        $client = new \DefaultAcsClient($iClientProfile);

        return $client;
    }

    /**
     * create the singleton
     * @return AliGreenAPI
     */
    public static function getInstance()
    {
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
     * @param $request
     */
    private function processResponse($request, $title, $type)
    {
        $client = $this->getClient();
        $response = $client->getAcsResponse($request);

        if(200 == $response->code){
            if(!$taskResults = $response->data)
                return $this->response(-200, '请重试！');

            $arr = $this->processSceneResult($taskResults, $title, $type);

            if(!$arr){
                return $this->response(200,[
                    'rate' => 100,
                    'describe' => '正常'
                ]);
            }else{
                return $this->response(-100, $arr);
            }

        }else{
            return $this->response(-200, '请重试！');
        }
    }

    /**
     * Date: 2020/2/8 18:33
     * @param $taskResults
     * @param $title
     * @param $type
     * @return mixed
     */
    private function processSceneResult($taskResults, $title, $type)
    {
        $arr = [];
        foreach ($taskResults as $value){
            foreach ($value->results as $v){
                $arr[] = $this->review($v->suggestion, $v->label, $v->rate, $v->scene);
            }
        }

        // 处理自定文字
        if(!array_filter($arr) && config('aliyun.content') && $type == 'text')
        {
            $title = Arr::wrap($title);

            foreach ($title as $v) {
                $arr[] = $this->reviewText($v);
            }
        }

        return current(array_filter($arr));
    }

    /**
     * Notes: 格式化数据
     * Date: 2019/8/12 17:40
     * @param $suggestion
     * @param $label
     * @param $rate
     * @return array
     */
    private function review($suggestion, $label, $rate, $scene)
    {
        $arr = [];
        if(in_array($suggestion, ['review', 'block'])){
            $arr = [
                'label' => $label,
                'rate' => $rate,
                'scene' => $scene,
                'describe' => $suggestion == 'review' ? '疑似' : '违规',
            ];
        }
        return $arr;
    }

    /**
     * Notes: 检测是否包含自定义文字
     * Date: 2019/8/12 17:45
     * @param $title
     * @return array
     */
    private function reviewText($title)
    {
        return Str::contains($title, config('aliyun.content')) ? [ 'rate' => 100, 'describe' => '违规'] : [];
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
    public function checkText($text)
    {
        if(empty($text)){
            return null;
        }

        $request = new Green\TextScanRequest();
        $request->setMethod("POST");
        $request->setAcceptFormat("JSON");

        if(is_array($text))
        {
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
            $request->setContent(json_encode(array("tasks" => $taskArr, "scenes" => array("antispam"))));

        }else if(is_string($text)){
            $task1 = array('dataId' =>  md5(uniqid()),
                'content' => $text
            );
            $request->setContent(json_encode(array("tasks" => array($task1), "scenes" => array("antispam"))));
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
    public function checkImg($img)
    {
        if(empty($img)){
            return null;
        }

        $request = new Green\ImageSyncScanRequest();
        $request->setMethod("POST");
        $request->setAcceptFormat("JSON");

        if(is_array($img))
        {
            $taskArr = array();
            foreach($img as $k => $v){
                $task = 'task'.$k;
                $$task = array('dataId' =>  md5(uniqid($task)),
                    'url' => $v,
                    'time' => round(microtime(true)*1000)
                );
                array_push($taskArr, $$task);
            }
            $request->setContent(json_encode(array("tasks" => $taskArr, "scenes" => config('aliyun.scenes'))));

        }else if(is_string($img)){
            $task1 = array('dataId' =>  md5(uniqid()),
                'url' => $img,
                'time' => round(microtime(true)*1000)
            );
            $request->setContent(json_encode(array("tasks" => array($task1), "scenes" => config('aliyun.scenes'))));
        }

        return $this->processResponse($request, $img, 'image');
    }

    /**
     * @param $code
     * @param $msg
     */
    private function response($code, $msg)
    {
        return [
            'code' => $code,
            'msg' => $msg,
        ];
    }
}
