<?php

namespace AlonePhp\Telegram\Bot\Expand;

trait Method{

    /**
     * 把发送文件的file_id保存到redis
     * @param string $key
     * @param string $val
     * @return void
     */
    protected function set_redis_cache(string $key, string $val): void {
        ($this->cacheCallback['set'])($this->getBotKey('id') . $key, $val);
    }

    /**
     * 从redis中获取发送文件的file_id
     * @param string $key
     * @return mixed
     */
    protected function get_redis_cache(string $key): mixed {
        return (($this->cacheCallback['get'])($this->getBotKey('id') . $key));
    }

    /**
     * 通过a.b.c.d获取数组内容
     * @param array           $array   要取值的数组
     * @param string|int|null $key     支持aa.bb.cc.dd这样获取数组内容
     * @param mixed           $default 默认值
     * @param string          $symbol  自定符号
     * @return mixed
     */
    public static function getArr(array $array, string|int|null $key, mixed $default = null, string $symbol = '.'): mixed {
        if (isset($key)) {
            if (isset($array[$key])) {
                $array = $array[$key];
            } else {
                $symbol = $symbol ?: '.';
                $arr = explode($symbol, trim($key, $symbol));
                foreach ($arr as $v) {
                    if (isset($v) && isset($array[$v])) {
                        $array = $array[$v];
                    } else {
                        $array = $default;
                        break;
                    }
                }
            }
        }
        return ($array ?? $default);
    }


    /**
     * array 每隔 N 个分一个array
     * 使用到TG
     * @param array         $array    要分隔的array
     * @param int|array     $n        int每行几个,array设置每行几个[3,3,2,2]
     * @param callable|null $callable 分隔时处理数据
     * @param array         $data
     * @param array         $line
     * @param int           $j
     * @return array
     */
    public static function arrayLine(array $array, int|array $n, callable|null $callable = null, array $data = [], array $line = [], int $j = 0): array {
        $callable = (!empty($callable) && is_callable($callable)
            ? $callable
            : function($v) {
                return $v;
            });
        foreach ($array as $k => $v) {
            $line[] = $callable($v, $k);
            $val = (is_array($n) ? ($n[$j] ?? ($val ?? 1)) : $n);
            if (count($line) == $val) {
                ++$j;
                $data[] = $line;
                $line = [];
            }
        }
        return array_merge($data, (!empty($line) ? [$line] : []));
    }

    /**
     * 删除第几个再重排
     * @param array|string $keyboard
     * @param string       $markup 删除的key,第几个
     * @param int|array    $line   重排
     * @return array
     */
    public static function delArrLine(array|string $keyboard, string $markup = '', int|array $line = []): array {
        $keyboard = is_array($keyboard) ? $keyboard : static::isJson($keyboard);
        if (!empty($keyboard) && !empty($markup)) {
            $markups = explode(',', $markup);
            if (!empty($markups)) {
                $i = 0;
                $arr = [];
                $lines = [];
                foreach ($keyboard as $val) {
                    $j = 0;
                    foreach ($val as $v) {
                        ++$i;
                        ++$j;
                        if (empty(in_array($i, $markups))) {
                            $arr[] = $v;
                        }
                    }
                    $lines[] = $j;
                }
                if (!empty($arr)) {
                    $keyboard = static::arrayLine($arr, ($line ?: $lines));
                }
            }
        }
        return $keyboard;
    }

    /**
     * 数组转Json
     * @param array|object $array
     * @param bool         $int 是否数字检查
     * @return false|string
     */
    public static function json(array|object $array, bool $int = true): bool|string {
        return $int ? \json_encode($array, JSON_NUMERIC_CHECK + JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES) : \json_encode($array, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
    }

    /**
     * 判断字符串是否json,返回array
     * @param mixed     $json
     * @param bool|null $associative
     * @param int       $depth
     * @param int       $flags
     * @return mixed
     */
    public static function isJson(mixed $json, bool $associative = true, int $depth = 512, int $flags = 0): mixed {
        $json = \json_decode((is_string($json) ? ($json ?: '') : ''), $associative, $depth, $flags);
        return (($json && \is_object($json)) || (\is_array($json) && $json)) ? $json : [];
    }
}