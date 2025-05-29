<?php

namespace AlonePhp\Telegram\Body;

use AlonePhp\Telegram\Bot;

/**
 * TG请求处理类
 */
class Process {

    //用户ID  唯一标识
    public string|int $user_id = '';

    //当前信息ID
    public string|int|array $message_id = '';

    //当前回复ID(机器人=用户ID,群ID,频道ID)
    public string|int $chat_id = '';

    //回调id
    public string|int $query_id = '';

    //信息小类(如普通信息是否为视频或者图片等)
    public string|int $msg_type = '';

    //收到文本信息
    public string|int $message_text = '';

    //回调data
    public string|int $query_data = '';

    //回调游戏名称
    public string|int $game_name = '';

    //用户帐号
    public string|int $user_name = '';

    //用户姓
    public string|int $first_name = '';

    //用户名
    public string|int $last_name = '';

    /**
     * 聊天类型,信息来源
     * bot     机器人
     * channel 频道
     * group   群组
     */
    public string $chat_type = '';

    //信息类型
    public string $message_type = '';

    //普通消息小类列表
    public static array $msg_type_list = [
        'photo'     => '图片',
        'video'     => '视频',
        'animation' => '动画',
        'audio'     => '音频',
        'voice'     => '语音',
        'document'  => '文档',
        'text'      => '文本'
    ];

    //当前命令
    public string|int $command = '';

    //当前命令参数
    public string|int $commnew = '';

    //投票结果
    public array $poll_list = [];

    //完整请求数据
    public array $body = [];

    //当前信息数据
    public array $post = [];

    //来源名称,如群名,频道名,bot=机器人
    public string|int $chat_title = '';

    //飞机语言
    public string $lang = '';

    //当前 update_id
    public string|int $update_id = '';

    //设置接收信息类型
    protected array $pull_type = [
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
    ];

    /**
     * Req->post有值 信息是有效的
     * @param array $body
     * @param array $pull_type 处理的信息类型
     */
    public function __construct(array $body, array $pull_type = []) {
        $this->pull_type = $pull_type ?: $this->pull_type;
        $this->classification($body);
    }
    /**
     *  ==============================================================
     *  ----------------------------获取信息---------------------------
     *  ==============================================================
     */

    /**
     * 没有处理过的数据
     * @param string|int|null $key
     * @param mixed           $default
     * @return mixed
     */
    public function body(null|string|int $key = null, mixed $default = ''): mixed {
        return Bot::getArr($this->body, $key, $default);
    }

    /**
     * 已处理过的数据
     * @param string|int|null $key
     * @param mixed           $default
     * @return mixed
     */
    public function post(null|string|int $key = null, mixed $default = ''): mixed {
        return Bot::getArr($this->post, $key, $default);
    }

    /**
     *  ==============================================================
     *  --------------------------信息分类处理--------------------------
     *  ==============================================================
     */

    /**
     * 信息分类处理
     * @param array $body
     * @return $this
     */
    protected function classification(array $body): static {
        $this->post = [];
        $this->body = $body;
        $this->update_id = $this->body('update_id');
        if (!empty($this->update_id)) {
            foreach ($this->pull_type as $k => $v) {
                if (is_array($v)) {
                    if (isset($this->body[$k])) {
                        $this->message_type = $k;
                        foreach ($v as $key) {
                            if (isset($this->body[$k][$key])) {
                                if (!empty($this->body[$k][$key])) {
                                    $this->post = $this->body($k, []);
                                    call_user_func([$this, $this->message_type . '_handle']);
                                }
                                break;
                            }
                        }
                    }
                } else {
                    if (isset($this->body[$v])) {
                        $this->message_type = $v;
                        if (!empty($this->body[$v])) {
                            $this->post = $this->body($v, []);
                            call_user_func([$this, $this->message_type . '_handle']);
                        }
                        break;
                    }
                }
            }
            switch ($this->chat_type) {
                case 'supergroup':
                    $this->chat_type = 'group';
                    break;
                case 'private':
                    $this->chat_type = 'bot';
                    break;
            }
        }
        return $this;
    }

    /**
     * 普通消息
     * @return $this
     */
    protected function message_handle(): static {
        $this->msg_type = 'text';                            //信息小类
        $this->chat_type = $this->post('chat.type');         //信息类型
        $this->user_id = $this->post('from.id');             //用户id
        $this->message_text = $this->post['text'] ?? '';     //收到信息
        $this->message_id = $this->post('message_id');       //信息id
        $this->chat_id = $this->post('chat.id');             //回复id / 群id /频道id
        $this->lang = $this->post('from.language_code');     //语言
        $this->user_name = $this->post('from.username');     //帐号
        $this->first_name = $this->post('from.first_name');  //姓
        $this->last_name = $this->post('from.last_name');    //名
        $this->chat_title = $this->post('chat.title', 'bot');//来源名称,如群名
        if (str_starts_with($this->message_text, '/')) {
            $command = ((explode('@', trim($this->message_text, '/'))[0]) ?? '');
            $commArr = explode(' ', $command);
            $this->command = $commArr[0];      //当前命令
            $this->commnew = $commArr[1] ?? '';//当前命令参数
        }
        foreach (static::$msg_type_list as $k => $v) {
            if (isset($this->post[$k])) {
                $this->msg_type = $k;//信息小类
                break;
            }
        }
        return $this;
    }


    /**
     * 回调查询（来自按钮点击）
     * @return $this
     */
    protected function callback_query_handle(): static {
        $this->msg_type = 'callback_text';
        $this->query_data = $this->post['data'] ?? '';//回调信息
        if (!empty($this->query_data)) {
            $this->msg_type = 'callback_data';
        }
        $this->game_name = $this->post['game_short_name'] ?? '';//游戏名称
        if (!empty($this->game_name)) {
            $this->msg_type = 'callback_game';
        }
        $this->query_id = $this->post('id');                  //回调id
        $this->user_id = $this->post('from.id');              //用户id
        $this->message_id = $this->post('message.message_id');//信息id
        $this->chat_type = $this->post('message.chat.type');  //信息类型
        $this->chat_id = $this->post('message.chat.id');      //回复id / 群id /频道id
        $this->user_name = $this->post('from.username');      //帐号
        $this->first_name = $this->post('from.first_name');   //姓
        $this->last_name = $this->post('from.last_name');     //名
        $this->lang = $this->post('from.language_code');      //语言
        $this->chat_title = $this->post('chat.title', 'bot'); //来源名称,如群名
        return $this;
    }

    /**
     * 匿名投票,接收投票详细
     * 投票
     * @return $this
     */
    protected function poll_handle(): static {
        $this->msg_type = 'poll';
        $options = $this->post('options', []);
        $this->poll_list['id'] = $this->post('id');                  //投票ID
        $this->poll_list['title'] = $this->post('question');         //投票名称
        $this->poll_list['count'] = $this->post('total_voter_count');//投票总数
        foreach ($options as $v) {
            $this->poll_list['list'][$v['text']] = $v['voter_count'];//名称=>数量
        }
        return $this;
    }

    /**
     * 实名投票 那个用户投了那个票
     * 投票答案
     * @return $this
     */
    protected function poll_answer_handle(): static {
        $this->msg_type = 'poll_answer';
        $this->user_id = $this->post('user.id');                                                                                          //用户id
        $this->user_name = $this->post('user.username');                                                                                  //帐号
        $this->first_name = $this->post('user.first_name') . !empty($last_name = $this->post('user.last_name')) ? (" " . $last_name) : "";//姓名
        $this->lang = $this->post('user.language_code');                                                                                  //语言
        $this->poll_list['id'] = $this->post('poll_id');                                                                                  //投票ID
        $this->poll_list['opt'] = $this->post('option_ids', []);                                                                          //选择投了第几个
        return $this;
    }

    /**
     * 频道消息
     * @return $this
     */
    protected function channel_post_handle(): static {
        $this->msg_type = 'channel_text';
        $this->message_text = $this->post('text');           //收到信息
        $this->message_id = $this->post('message_id');       //信息id
        $this->chat_type = $this->post('chat.type');         //信息类型
        $this->chat_id = $this->post('chat.id');             //回复id / 群id /频道id
        $this->chat_title = $this->post('chat.title', 'bot');//来源名称,如群名
        foreach (static::$msg_type_list as $k => $v) {
            if (isset($this->post[$k])) {
                $this->msg_type = 'channel_' . $k;//信息小类
                break;
            }
        }
        return $this;
    }

    /**
     * 编辑过的普通消息
     * @return $this
     */
    protected function edited_message_handle(): static {
        $this->message_handle();
        $this->msg_type = 'edit_' . $this->msg_type;
        return $this;
    }

    /**
     * 编辑过的频道消息
     * @return $this
     */
    protected function edited_channel_post_handle(): static {
        $this->channel_post_handle();
        $this->msg_type = 'edit_' . $this->msg_type;
        return $this;
    }

    /**
     * 内联查询 等待开发
     * @return $this
     */
    protected function inline_query_handle(): static {
        $this->msg_type = 'inline';
        return $this;
    }

    /**
     * 选择的内联结果 等待开发
     * @return $this
     */
    protected function chosen_inline_result_handle(): static {
        $this->msg_type = 'inline_result';
        return $this;
    }

    /**
     * 运输查询（用于购物） 等待开发
     * @return $this
     */
    protected function shipping_query_handle(): static {
        $this->msg_type = 'shipping';
        return $this;
    }

    /**
     * 预检查查询（用于购物） 等待开发
     * @return $this
     */
    protected function pre_checkout_query_handle(): static {
        $this->msg_type = 'checkout';
        return $this;
    }
}