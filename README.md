```text
composer require alone-php/telegram
```

### 发送使用例

```php

$bot = alone_bot("Use this token to access the HTTP API");

//设置发送id (机器人=用户ID,群ID,频道ID),array=群发
$bot->chat_id('id');

# 菜单键盘和信息键盘只能二选一
//设置 菜单键盘
$bot->set_keyboard();
//设置 信息键盘
$bot->set_inline();

//发送信息
$bot->sendMessage('普通文本信息');
//更新多发送方法请查看类文件

//发送响应array
print_r($bot->array());

//调试输出 请求和响应信息
print_r($bot->debug());
```

### 设置和获取信息方法

```php
//通过网址接收信息
$bot->setWebhook();

//删除网址接收信息
$bot->deleteWebhook();

//主动获取信息,使用此方法就不能设置网址
$bot->getUpdates();
//收到的机器人信息
print_r($bot->array());

//设置机器人命令列表
$bot->setMyCommands();

//设置机器人聊天左侧按钮菜单
$bot->setChatMenuButton('web_app','打开网址','https://google.com');
//删除机器人聊天左侧按钮菜单
$bot->setChatMenuButton('commands');
$bot->setChatMenuButton();
```

## 接收信息处理

```php

$bot = alone_bot_body("收到的机器人信息",[
        //普通消息
        'message',
        //回调查询（来自按钮点击）
        'callback_query',
        //匿名投票,接收投票详细
        'poll',
        //实名投票 那个用户投了那个票
        'poll_answer',
        //频道消息
        'channel_post',
        //编辑过的普通消息
        'edited_message',
        //编辑过的频道消息
        'edited_channel_post',
        //内联查询
        'inline_query',
        //选择的内联结果
        'chosen_inline_result',
        //运输查询（用于购物）
        'shipping_query',
        //预检查查询（用于购物）
        'pre_checkout_query',
    ]);
    
//为空时不接收此信息,不作处理
$bot->post;

// 用户ID  唯一标识
echo $bot->user_id;

// 当前信息ID
echo $bot->message_id;

// 当前回复ID(机器人=用户ID,群ID,频道ID)
echo $bot->chat_id;

// 回调id
echo $bot->query_id;

// 信息小类(如普通信息是否为视频或者图片等)
echo $bot->msg_type;

// 收到文本信息
echo $bot->message_text;

// 回调信息
echo $bot->query_data;

// 回调游戏名称
echo $bot->game_name;

// 用户帐号
echo $bot->user_name;

// 用户姓
echo $bot->first_name;

// 用户名
echo $bot->last_name;

// 聊天类型 bot=机器人 channel=频道 group=群组
echo $bot->chat_type;

// 信息类型
echo $bot->message_type;

// 当前命令
echo $bot->command;

// 当前命令参数
echo $bot->commnew;

// 投票结果
echo $bot->poll_list;

// 完整请求数据
echo $bot->body;

// 当前信息数据
echo $bot->post;

// 来源名称,如群名,频道名,bot=机器人
echo $bot->chat_title;

// 飞机语言
echo $bot->lang;

// 当前 update_id
echo $bot->update_id;
```