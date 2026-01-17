<?php

namespace AlonePhp\Telegram;

use AlonePhp\Telegram\Bot\Set;
use AlonePhp\Telegram\Bot\Get;
use AlonePhp\Telegram\Bot\Send;
use AlonePhp\Telegram\Bot\Edit;
use AlonePhp\Telegram\Bot\Command;
use AlonePhp\Telegram\Body\Process;
use AlonePhp\Telegram\Bot\Expand\Curl;
use AlonePhp\Telegram\Bot\CallbackQuery;
use AlonePhp\Telegram\Bot\Expand\Method;
use AlonePhp\Telegram\Bot\Expand\SendArr;
use AlonePhp\Telegram\Bot\AnswerInlineQuery;

/**
 * TG发送类
 * https://core.telegram.org/bots/api
 */
class Bot {
    use Method, Curl, Set, Command, Send, Edit, CallbackQuery, Get, SendArr, AnswerInlineQuery;

    //接口
    public string $url = 'https://api.telegram.org/';
    //机器人key
    public string $key = '';
    //回调id
    public string|int|null $query_id = '';
    //回复id
    public string|int|array|null $chat_id = '';
    //信息id
    public string|int|null $message_id = '';
    //信息按钮
    public string|array $reply_markup = [];
    //调试请求信息
    public array $request = [];
    //返回数据
    public mixed $body = [];
    //已处理的返回数据
    protected mixed $res = [];
    //设置单请求代理
    protected static array $proxy = [];
    //设置全局代理
    protected array $ip = [];
    // 是否使用 curl_multi_init
    protected bool $multi = true;
    //cache设置
    protected array        $cacheCallback = [];
    protected static array $sendCacheList = [];

    /**
     * 设置key
     * @param string $key
     * @return static
     */
    public static function tokenApi(string $key): static {
        return new static($key);
    }

    /**
     * Body->post有值 信息是有效的
     * @param array $body      接收到的内容
     * @param array $pull_type 处理的信息类型
     * @return Process
     */
    public static function bodyApi(array $body, array $pull_type = []): Process {
        return new Process($body, $pull_type);
    }

    /**
     * @param string $key
     */
    public function __construct(string $key) {
        $this->key = $key;
        $this->cacheCallback = [
            'set' => function($key, $val) {
                static::$sendCacheList[$key] = $val;
            },
            'get' => function($key) {
                return static::$sendCacheList[$key] ?? null;
            },
        ];
    }

    /**
     * 使用send方法时使用到,主要是保存文件标识,防止重复上传文件
     * 设置cache包,推荐使用redis保存24小时
     * @param callable $set
     * @param callable $get
     * @return $this
     */
    public function setCache(callable $set, callable $get): static {
        $this->cacheCallback = [
            'set' => $set,
            'get' => $get,
        ];
        return $this;
    }

    /**
     * 是否使用 curl_multi_init
     * @param bool $multi
     * @return $this
     */
    public function multi(bool $multi): static {
        $this->multi = $multi;
        return $this;
    }

    /**
     * 设置发送chat_id
     * array=群发
     * 注意:带有文件的群发,先发一条,判断成功再群发
     * @param string|int|array|null $chat_id
     * @return $this
     */
    public function chat_id(string|int|array|null $chat_id): static {
        $this->chat_id = $chat_id;
        return $this;
    }

    /**
     * 设置发送message_id
     * @param string|int|null $message_id
     * @return $this
     */
    public function message_id(string|int|null $message_id): static {
        $this->message_id = $message_id;
        return $this;
    }

    /**
     * 设置回调id
     * @param string|int|null $query_id
     * @return $this
     */
    public function query_id(string|int|null $query_id): static {
        $this->query_id = $query_id;
        return $this;
    }

    /**
     * 设置 菜单键盘
     * https://core.telegram.org/bots/api#replykeyboardmarkup
     * https://core.telegram.org/bots/api#keyboardbutton
     * @param array     $list
     * @param int|array $line
     * @param array     $keyboard
     * @param bool      $merge
     * @return $this
     */
    public function set_keyboard(array $list = [], int|array $line = 2, array $keyboard = [], bool $merge = true): static {
        $keyboard = $merge ? array_merge([
            'is_persistent'     => true,
            // 是否为一次性键盘
            'one_time_keyboard' => false,
            // 自适应键盘大小
            'resize_keyboard'   => true,
            // false表示对所有用户生效，true表示只对特定用户生效
            'selective'         => false
        ], $keyboard) : $keyboard;
        if (!empty($list)) {
            $reply_markup = static::arrayLine($list, $line);
            $keyboard['keyboard'] = $reply_markup;
        }
        $this->reply_markup = $keyboard;
        return $this;
    }

    /**
     * 设置 信息键盘
     * https://core.telegram.org/bots/api#inlinekeyboardbutton
     * @param array     $list
     * @param int|array $line
     * @param array     $keyboard
     * @return $this
     */
    public function set_inline(array $list = [], int|array $line = 2, array $keyboard = []): static {
        if (!empty($list)) {
            $reply_markup = static::arrayLine($list, $line);
            $keyboard['inline_keyboard'] = $reply_markup;
        }
        $this->reply_markup = $keyboard;
        return $this;
    }

    /**
     * 设置单请求代理ip,优先1
     * @param string     $ip
     * @param string|int $port
     * @param string     $user
     * @param string     $type
     * @param string     $auth
     * @return $this
     */
    public function ip(string $ip, string|int $port = '', string $user = '', string $type = 'http', string $auth = 'basic'): static {
        $this->ip = [
            'ip'   => $ip,
            'port' => $port,
            'user' => $user,
            'type' => $type,
            'auth' => $auth,
        ];
        return $this;
    }

    /**
     * 设置全局代理ip,优先2
     * @param string     $ip
     * @param string|int $port
     * @param string     $user
     * @param string     $type
     * @param string     $auth
     * @return void
     */
    public static function proxy(string $ip, string|int $port = '', string $user = '', string $type = 'http', string $auth = 'basic'): void {
        static::$proxy = [
            'ip'   => $ip,
            'port' => $port,
            'user' => $user,
            'type' => $type,
            'auth' => $auth,
        ];
    }

    /**
     * 获取机器人id key
     * @param bool|string $type true返回array false返回key,string返回指定值
     * @return array|string
     */
    public function getBotKey(bool|string $type = false): array|string {
        if (!empty($type)) {
            $arr = explode(':', trim($this->key));
            $data['id'] = $arr[0] ?? '';
            $data['key'] = $arr[1] ?? '';
            return (is_bool($type) ? $data : ($data[$type] ?? $data));
        }
        return $this->key;
    }

    /**
     * 调试信息
     * @param bool $array
     * @return array
     */
    public function debug(bool $array = false): array {
        return ['request' => $this->request(), 'response' => $array ? $this->array() : $this->body()];
    }

    /**
     * 获取请求信息
     * @return mixed
     */
    public function request(): mixed {
        return count($this->request) == 1 ? ($this->request[key($this->request)] ?? $this->request) : $this->request;
    }

    /**
     * 获取返回信息
     * @return mixed
     */
    public function body(): mixed {
        return count($this->body) == 1 ? ($this->body[key($this->body)] ?? $this->body) : $this->body;
    }

    /**
     * 获取返回信息array
     * @param string|int|null $key
     * @param mixed           $default
     * @param array           $res
     * @return mixed
     */
    public function array(null|string|int $key = null, mixed $default = '', array $res = []): mixed {
        if (!empty($this->body) && empty($this->res)) {
            foreach ($this->body as $k => $v) {
                $res[$k] = !empty($arr = static::isJson($v)) ? $arr : $v;
            }
            $arr = count($res) == 1 ? ($res[key($res)] ?? $res) : $res;
            $this->res = is_array($arr) ? $arr : [];
        }
        return static::getArr($this->res, $key, $default);
    }

    /**
     * 设置键盘
     * @param array $conf
     * @return array|string
     */
    protected function reply_markup(array $conf = []): array|string {
        if (!empty($this->reply_markup) && !empty($json = static::json($this->reply_markup))) {
            $conf['reply_markup'] = $json;
        }
        return $conf;
    }

    /**
     * @param array $conf
     * @param array $arr
     * @param array $array
     * @return array
     */
    protected function get_conf(array $conf, array $arr, array $array = []): array {
        if (!empty($arr)) {
            foreach ($conf as $k => $v) {
                if (is_numeric($k)) {
                    if (isset($arr[$v])) {
                        $array[$v] = $arr[$v];
                    }
                } else {
                    $array[$k] = static::getArr($arr, $k, $v);
                }
            }
        }
        return $array;
    }
}