<?php

namespace AlonePhp\Telegram\Bot\Expand;

/**
 * 信息发送类
 * keyboard=菜单键盘 inline=信息键盘
 * 设置 菜单键盘
 * https://core.telegram.org/bots/api#replykeyboardmarkup
 * https://core.telegram.org/bots/api#keyboardbutton
 * 设置 信息键盘
 * https://core.telegram.org/bots/api#inlinekeyboardbutton
 * callback_data
 * web_app 打开小程序
 * url 打开网站
 * callback_game = 发送游戏
 * switch_inline_query= 转发信息 到指定尖
 * switch_inline_query_current_chat =回复指定ID
 * login_url 要设置机器人域名
 */
trait SendArr{

    //发送文件id
    protected mixed $file_id = '';

    //普通消息小类列表
    protected static array $msg_type_list = [
        'photo'     => '图片',
        'video'     => '视频',
        'animation' => '动画',
        'audio'     => '音频',
        'voice'     => '语音',
        'document'  => '文档',
        'text'      => '文本'
    ];

    //发送设置
    protected array $send_conf = [
        'type' => 'msg',//发送类型(msg=普通信息,poll=投票,game=发送游戏,dice=发送骰子)
        //发送游戏
        'game' => [
            'title'  => '开始游戏',//开始游戏按钮名称,默认第一个
            'name'   => 'demo',//游戏标识
            'conf'   => [],//发送配置 可选
            'button' => [
                'type' => 'inline',//keyboard=菜单键盘 inline=信息键盘
                'line' => 1,//按钮排例
                'list' => [
                    ['text' => '联系客服']
                ],
                'conf' => []//按钮配置 可选
            ]
        ],
        //发送骰子
        'dice' => [
            'emo'  => 1,//骰子类型1-6
            'conf' => [],//发送配置 可选
        ],
        //投票
        'poll' => [
            'title'     => '投票名称',//投票名称
            'option'    => ["PHP", "JAVA"],//投票内容2-20个
            'anonymous' => true,//是否匿名投票,可选,默认true
            'conf'      => [],//发送配置 可选
            'button'    => [
                'type' => 'inline',//keyboard=菜单键盘 inline=信息键盘
                'line' => 1,//按钮排例
                'list' => [
                    ['text' => '联系客服']
                ],
                'conf' => []//按钮配置 可选
            ]
        ],
        //普通信息
        'msg'  => [
            'type'      => 'text',//信息类型
            'text'      => '信息内容',//信息内容
            //@开头为本地图片
            'photo'     => '',//'图文',
            'video'     => '',//'视频',
            'animation' => '',//'动画',
            'audio'     => '',//'音频',
            'voice'     => '',//'语音',
            'document'  => '',//'文档',
            'redis'     => true,//是否把file_id保存到redis,可选,默认true
            'conf'      => [],//发送配置,可选
            'button'    => [
                'type' => 'inline',//keyboard=菜单键盘 inline=信息键盘
                'line' => 1,//按钮排例
                'list' => [
                    ['text' => '联系客服']
                ],
                'conf' => []//按钮配置
            ]
        ]
    ];

    /**
     * 通用发送
     * @param $send_conf
     * @return $this
     */
    public function send($send_conf): static {
        $type = $send_conf['type'] ?? 'msg';
        $this->send_conf = $send_conf[$type] ?? ($send_conf['send'] ?? []);
        switch ($type) {
            case "game":
                $title = $this->send_conf('title');
                if (!empty($title)) {
                    $button_list = $this->send_conf('button.list', []);
                    if (empty($button_list) || empty(isset($button_list[key($button_list)]['callback_game']))) {
                        $button = $this->send_conf('button.list', []);
                        array_unshift($button, ['text' => $title, 'callback_game' => $this->send_conf('name')]);
                        $this->send_conf['button']['list'] = $button;
                    }
                }
                $this->set_button()->send_game();
                break;
            case "poll":
                $this->set_button()->send_poll();
                break;
            case "dice":
                $this->sendDice($this->send_conf('emo', 1), $this->send_conf('conf', []));
                break;
            default:
                $this->set_button()->send_msg();
                break;
        }
        return $this;
    }


    /**
     * 发送信息
     * @return $this
     */
    protected function send_msg(): static {
        $msg_type = $this->send_conf('type', 'text');  //信息类型
        $msg_text = $this->send_conf('text');          //信息内容
        $msg_conf = $this->send_conf('conf', []);      //发送配置
        $msg_redis = $this->send_conf['redis'] ?? true;//是否把文件保存到redis
        if ($msg_type != 'text' && !empty($msg_file = $this->send_conf($msg_type, $this->send_conf('file')))) {
            if (!empty($msg_redis)) {
                $send_file = $this->get_file_id($msg_file);
            }
            if (empty($send_file)) {
                $send_file = $msg_file;//本地图片
                if (str_starts_with($msg_file, '@')) {
                    $new_file = substr($msg_file, 1);
                    $local_file = realpath($new_file);
                    if (!empty($local_file)) {
                        $send_file = new \CURLFile($local_file);//本地图片
                    }
                }
            }
            if (!empty($send_file)) {
                switch ($msg_type) {
                    case "photo":
                        $this->sendPhoto($send_file, $msg_text, $msg_conf);
                        break;
                    case "audio":
                        $this->sendAudio($send_file, $msg_text, $msg_conf);
                        break;
                    case "voice":
                        $this->sendVoice($send_file, $msg_text, $msg_conf);
                        break;
                    case "video":
                        $this->sendVideo($send_file, $msg_text, $msg_conf);
                        break;
                    case "animation":
                        $this->sendAnimation($send_file, $msg_text, $msg_conf);
                        break;
                    case "document":
                        $this->sendDocument($send_file, $msg_text, $msg_conf);
                        break;
                }
                //保存文件到redis
                if (!empty($msg_redis)) {
                    $this->save_file_id($msg_file);
                }
                return $this;
            }

        }
        if (!empty($msg_text)) {
            $this->sendMessage($msg_text, $msg_conf);
        }
        return $this;
    }

    /**
     * 发送投票
     * @return $this
     */
    protected function send_poll(): static {
        $title = $this->send_conf('title');
        $option = $this->send_conf('option', []);
        if (!empty($title) && !empty($option)) {
            $anonymous = $this->send_conf['anonymous'] ?? true;
            $type = $this->send_conf('type', 'regular');
            $config = $this->send_conf('conf', []);
            $this->sendPoll($title, $option, $anonymous, $type, $config);
        }
        return $this;
    }

    /**
     * 发送游戏
     * @return $this
     */
    protected function send_game(): static {
        if (!empty($name = $this->send_conf('name'))) {
            $config = $this->send_conf('conf', []);
            $this->sendGame($name, $config);
        }
        return $this;
    }

    /**
     * 设置按钮
     * @param array $keyboard
     * @return $this
     */
    protected function set_button(array $keyboard = []): static {
        if (!empty($button_list = $this->send_conf('button.list', []))) {
            foreach ($button_list as $v) {
                $keyboard[] = $v;
            }
            if (!empty($keyboard)) {
                $button_line = $this->send_conf('button.line', 2); //按钮排例
                $button_conf = $this->send_conf('button.conf', []);//按钮配置
                if ($this->send_conf('button.type', 'inline') == 'keyboard') {
                    $this->set_keyboard($keyboard, $button_line, $button_conf);//菜单键盘
                } else {
                    $this->set_inline($keyboard, $button_line, $button_conf);//信息键盘
                }
            }
        }
        return $this;
    }

    /**
     * @param string|int|null $key
     * @param mixed           $default
     * @return mixed
     */
    protected function send_conf(null|string|int $key = null, mixed $default = ''): mixed {
        return static::getArr($this->send_conf, $key, $default);
    }

    /**
     * 获取redis的file_id
     * @param  $msg_file
     * @return mixed
     */
    protected function get_file_id($msg_file): mixed {
        if (is_string($msg_file)) {
            $redis_key = substr(md5($msg_file), 8, 16);
            $this->file_id = $this->get_redis_cache($redis_key);
        }
        return $this->file_id;
    }

    /**
     * 发送成功后把file_id保存到redis
     * @param string $msg_file
     * @return bool
     */
    protected function save_file_id(string $msg_file): bool {
        $result = ((count($this->body) > 1) ? $this->array(key($this->body) . '.result') : $this->array('result'));
        if (!empty($result)) {
            foreach (static::$msg_type_list as $k => $v) {
                if (isset($result[$k])) {
                    if ($k == 'photo') {
                        if (!empty($result[$k])) {
                            $end = end($result[$k]);
                            if (!empty($end)) {
                                $file_id = $end['file_id'] ?? '';
                            }
                        }
                        break;
                    } else {
                        $file_id = static::getArr($result[$k], 'file_id');
                        if (!empty($file_id)) {
                            break;
                        }
                    }
                }
            }
            if (!empty($file_id)) {
                $redis_key = substr(md5($msg_file), 8, 16);
                $this->file_id = $file_id;
                $this->set_redis_cache($redis_key, $file_id);
                return true;
            }
        }
        return false;
    }
}