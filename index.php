<?php
date_default_timezone_set('Asia/Shanghai');

session_start();

header('Access-Control-Allow-Origin:*');

// require_once __DIR__ . '/vendor/autoload.php';

function init_redis()
{
    $parameters['host'] = 'localhost';
    $parameters['port'] = 6379;
    $parameters['scheme'] = 'tcp';
    $parameters['timeout'] = 1;
    $redisdb = new Redis();
    $redisdb->connect($parameters['host'], $parameters['port'], 1);
    return $redisdb;
    // return new Predis\Client($parameters);
}

$p1 = [
    "冬枣",
    "富士苹果",
    "巨峰葡萄",
    "木瓜",
    "李子",
    "杏子",
    "枇杷",
    "柠檬",
    "樱桃",
    "沙田柚",
    "油桃",
    "猕猴桃",
    "甜瓜",
    "砂糖橘",
    "脐橙",
    "芒果",
    "草莓",
    "荔枝",
    "菠萝",
    "西瓜",
    "香蕉",
    "鸭梨",
];

$p2 = [
    "丝瓜",
    "佛手瓜",
    "光皮黄瓜",
    "冬瓜",
    "冬笋",
    "凤尾菇",
    "南瓜",
    "圆茄子",
    "土豆",
    "大白菜",
    "大葱",
    "大蒜",
    "小白菜",
    "小葱",
    "尖椒",
    "山药",
    "平菇",
    "心里美萝卜",
    "木耳菜",
    "杏鲍菇",
    "樱桃西红柿",
    "毛豆",
    "水萝卜",
    "油菜",
    "油麦菜",
    "洋白菜",
    "玉米棒",
    "瓠子",
    "生姜",
    "生菜",
    "白灵菇",
    "白萝卜",
    "白蒜5.0公分",
    "福鼎芋",
    "空心菜",
    "竹笋",
    "红尖椒",
    "红椒",
    "红萝卜",
    "绿尖椒",
    "绿豆芽",
    "胡萝卜",
    "良薯",
    "芋头",
    "芥菜",
    "芹菜",
    "苋菜",
    "苦瓜",
    "茄子",
    "茭白",
    "茴香",
    "茶树菇",
    "茼蒿",
    "草菇",
    "荷兰豆",
    "莲藕",
    "莴笋",
    "菜瓜",
    "菜花",
    "菜苔",
    "菠菜",
    "葱头",
    "蒜苗",
    "蒜薹",
    "蘑菇",
    "西兰花",
    "西洋芹",
    "西红柿",
    "西葫芦",
    "豆角",
    "豇豆",
    "豌豆尖",
    "金针菇",
    "银耳",
    "长茄子",
    "青椒",
    "青笋",
    "韭苔",
    "韭菜",
    "韭菜花",
    "韭黄",
    "香菇",
    "香菜",
    "马蹄",
    "鸡腿菇",
    "黄豆芽",
];

$product = ["水果" => $p1, "蔬菜" => $p2];

function web()
{
    global $R;
    global $act_flow;
    global $act_flow_user;
    $res = $R->get($act_flow);
    // if (!$res) {
    //     $res = '富士苹果';
    // }

    $data['selected'] = $res;

    $act_flow_user_res = $R->get($act_flow_user);

    if ($act_flow_user_res) {
        $data['user'] = intval($act_flow_user_res) ? intval($act_flow_user_res) : 1;
    } else {
        $data['user'] = null;
    }

    echo json_encode(['data' => $data]);
    die;
}

function web_logout()
{
    global $R;
    global $act_flow_user;
    global $act_flow;
    $R->del($act_flow_user);
    $R->del($act_flow);
    echo json_encode(['data' => 'ok']);
    die;
}

function phone()
{
    global $product;
    global $R;
    global $act_flow_user;
    global $act_flow;
    $data['product'] = $product;

    $res = $R->get($act_flow_user);
    if (!$res) { // 没人
        $R->set($act_flow_user, session_id());
        $selected = $R->get($act_flow);
        $data['selected'] = $selected;
        echo json_encode($data);die;
    } else {
        if ($res == session_id()) {
            $selected = $R->get($act_flow);
            $data['selected'] = $selected;
            echo json_encode($data);die;
        }
    }
    echo json_encode(['data' => 'not get it']);
    die;
}

function phone_logout()
{
    global $R;
    global $act_flow_user;
    global $act_flow;

    $res = $R->get($act_flow_user);
    if ($res) {
        if ($res == session_id()) {
            $R->del($act_flow_user);
            $R->del($act_flow);
        }
    }
    echo json_encode(['data' => 'ok']);
    die;
}

function phone_select_product()
{
    global $R;
    global $act_flow_user;
    global $act_flow;
    global $p1;
    global $p2;

    $product = isset($_REQUEST['product']) ? $_REQUEST['product'] : '';
    if (!in_array($product, $p1) && !in_array($product, $p2)) {
        echo json_encode(['data' => 'no this product']);
        die;
    }

    $res = $R->get($act_flow_user);
    if ($res) {
        if ($res == session_id()) {
            $R->set($act_flow, $product);
            echo json_encode(['data' => $product]);
            die;
        }
    }
    echo json_encode(['data' => 'not auth']);
    die;
}

$R = init_redis();

$act_flow_user = 'act_flow_user';
$act_flow = 'act_flow';

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

switch ($action) {
    case 'web':
        web();
        break;
    case 'web-logout':
        web_logout();
        break;
    case 'phone':
        phone();
        break;
    case 'phone-select':
        phone_select_product();
        break;
    case 'phone-logout':
        phone_logout();
        break;
    case 'get-phpinfo':
        phpinfo();
        break;
    default:
        web();
}
