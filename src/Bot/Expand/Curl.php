<?php

namespace AlonePhp\Telegram\Bot\Expand;

trait Curl {
    /**
     * @param string       $path      请求路径
     * @param array|string $data      请求数据
     * @param bool|string  $data_type [true|false|json] true=http_build_query,false=原样提交,json
     * @return $this
     */
    public function curl(string $path, array|string $data = [], bool|string $data_type = false): static {
        //判断群发
        if (is_array($data)) {
            if (!empty($chat_ids = static::getArr($data, 'chat_id'))) {
                if (is_array($chat_ids)) {
                    foreach ($chat_ids as $v) {
                        $send[] = [
                            'path'      => $path,
                            'data'      => array_merge($data, ['chat_id' => $v]),
                            'data_type' => $data_type,
                        ];
                    }
                    if (!empty($send)) {
                        $this->call($send);
                    }
                    return $this;
                }
            }
        }
        return $this->call([['path' => $path, 'data' => $data, 'data_type' => $data_type]]);
    }

    /**
     * @param array $arr
     * @return $this
     */
    protected function call(array $arr): static {
        $this->res = [];
        $this->body = [];
        $this->request = [];
        $this->reply_markup = [];
        if ($this->multi) {
            return $this->callMulti($arr);
        }
        return $this->callInit($arr);
    }

    /**
     * @param array $arr
     * @return $this
     */
    protected function callInit(array $arr): static {
        foreach ($arr as $k => $v) {
            $v['time'] = time();
            $v['url'] = trim($this->url, '/') . '/bot' . $this->key . '/' . trim(trim($v['path']), '/');
            $init = curl_init();
            curl_setopt($init, CURLOPT_URL, $v['url']);
            $curl = static::curl_set($v);
            $curl = $this->getProxy($curl);
            foreach ($curl as $ck => $cv) {
                curl_setopt($init, $ck, $cv);
            }
            $response = curl_exec($init);
            $this->body[$k] = curl_errno($init) ? curl_error($init) : $response;
            $this->request[$k] = [
                'url'       => $v['url'],
                'data'      => $v['data'] ?? [],
                'data_type' => $v['data_type'] ?? false,
                'exec_time' => (microtime(true) - $v['time']),
                'end_time'  => date("Y-d-m H:i:s")
            ];
            curl_close($init);
        }
        return $this;
    }

    /**
     * @param array $arr
     * @return $this
     */
    protected function callMulti(array $arr): static {
        $cache = [];
        $this->res = [];
        $this->body = [];
        $this->request = [];
        $this->reply_markup = [];
        $multi = curl_multi_init();
        foreach ($arr as $k => $v) {
            $v['url'] = trim($this->url, '/') . '/bot' . $this->key . '/' . trim(trim($v['path']), '/');
            $conn[$k] = curl_init($v['url']);
            $cache[$k] = array_merge($v, ['time' => microtime(true)]);
            $curl_arr = static::curl_set($v);
            curl_setopt_array($conn[$k], $this->getProxy($curl_arr));
            curl_multi_add_handle($multi, $conn[$k]);
        }
        do {
            $exec = curl_multi_exec($multi, $active);
            if ($active) {
                curl_multi_select($multi, 12);
            }
        } while ($active && $exec == CURLM_OK);
        if (!empty($conn)) {
            foreach ($conn as $k => $v) {
                $this->body[$k] = curl_multi_getcontent($v);//获得返回信息
                $this->request[$k] = [
                    'url'       => $cache[$k]['url'] ?? '',
                    'data'      => $cache[$k]['data'] ?? [],
                    'data_type' => $cache[$k]['data_type'] ?? false,
                    'exec_time' => (microtime(true) - ($cache[$k]['time'] ?? 0)),
                    'end_time'  => date("Y-d-m H:i:s")
                ];
                curl_close($v);                       //关闭语柄
                curl_multi_remove_handle($multi, $v); //释放资源
            }
        }
        curl_multi_close($multi);
        return $this;
    }

    /**
     * @param array $curl_arr
     * @return array
     */
    protected function getProxy(array $curl_arr = []): array {
        //设置代理ip
        if (!empty($proxy = ($this->ip)) || !empty($proxy = (static::$proxy))) {
            if (!empty($ip = ($proxy['ip'] ?? '')) && !empty($port = ($proxy['port'] ?? ''))) {
                $curl_arr[CURLOPT_PROXY] = $ip;
                $curl_arr[CURLOPT_PROXYPORT] = $port;
                if (!empty($user = ($proxy['user'] ?? ''))) {
                    $curl_arr[CURLOPT_PROXYUSERPWD] = $user;
                }
                if (!empty($proxy_type = ($proxy['type'] ?? ''))) {
                    $proxy_types = ($proxy_type == 'http' ? CURLPROXY_HTTP : ($proxy_type == 'socks5' ? CURLPROXY_SOCKS5 : $proxy_type));
                    $curl_arr[CURLOPT_PROXYTYPE] = $proxy_types;
                }
                if (!empty($proxy_auth = ($proxy['auth'] ?? ''))) {
                    $proxy_auths = ($proxy_auth == 'basic' ? CURLAUTH_BASIC : ($proxy_auth == 'ntlm' ? CURLAUTH_NTLM : $proxy_auth));
                    $curl_arr[CURLOPT_PROXYAUTH] = $proxy_auths;
                }
            }
        }
        return $curl_arr;
    }

    /**
     * @param array $conf
     * @return array
     */
    protected static function curl_set(array $conf): array {
        if (!empty($data = ($conf['data'] ?? []))) {
            $curl_arr[CURLOPT_POST] = true;
            $curl_arr[CURLOPT_CUSTOMREQUEST] = 'POST';
            $type = ($conf['data_type'] ?? false);
            if ($type == 'json') {
                $curl_arr[CURLOPT_HTTPHEADER] = ['Content-Type: application/json'];
            } elseif (!empty($type) && is_array($data)) {
                $data = http_build_query($data);
                $curl_arr[CURLOPT_HTTPHEADER] = ['Content-Length' => strlen($data)];
            }
            $curl_arr[CURLOPT_POSTFIELDS] = $data;
        }
        //连接时间,设置为0，则无限等待
        $curl_arr[CURLOPT_CONNECTTIMEOUT] = 12;
        //超时时间,设置为0，则无限等待
        $curl_arr[CURLOPT_TIMEOUT] = 12;
        //否检查证书,默认不检查
        $curl_arr[CURLOPT_SSL_VERIFYPEER] = false;
        //设置成 2，会检查公用名是否存在，并且是否与提供的主机名匹配。 0 为不检查名称。 在生产环境中，这个值应该是 2（默认值）
        $curl_arr[CURLOPT_SSL_VERIFYHOST] = false;
        //自动跳转时设置开启头部
        $curl_arr[CURLOPT_FOLLOWLOCATION] = false;
        //true 将curl_exec()获取的信息以字符串返回，而不是直接输出。
        $curl_arr[CURLOPT_RETURNTRANSFER] = true;
        //true 时将不输出 BODY 部分。同时 Mehtod 变成了 HEAD。修改为 false 时不会变成 GET
        $curl_arr[CURLOPT_NOBODY] = false;
        //是否返回头部信息
        $curl_arr[CURLOPT_HEADER] = false;
        return $curl_arr;
    }
}