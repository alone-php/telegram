<?php

namespace AlonePhp\Telegram\Bot;

trait CallbackQuery{
    /**
     * 回调游戏
     * @param string|int $url
     * @return $this
     */
    public function callbackGame(string|int $url): static {
        return $this->curl('answerCallbackQuery', ['callback_query_id' => $this->query_id, 'url' => $url]);
    }

    /**
     * 回调信息
     * @param string|int $content 提示内容
     * @param bool       $alert   是否弹窗
     * @return $this
     */
    public function callbackText(string|int $content, bool $alert = false): static {
        return $this->curl('answerCallbackQuery', ['callback_query_id' => $this->query_id, 'text' => $content, 'show_alert' => $alert]);
    }

    /**
     * 键盘回调
     * 使用此方法可发送对从内联键盘发送的回调查询的答案。答案将作为聊天屏幕顶部的通知或警报显示给用户。成功时，返回 True。
     * https://core.telegram.org/bots/api#answercallbackquery
     * @param array $conf
     * @return $this
     */
    public function answerCallbackQuery(array $conf = []): static {
        $conf['callback_query_id'] = $this->query_id;
        $data = $this->get_conf([
            'callback_query_id',//要回答的查询的唯一标识符
            'text',//通知文本。如果没有指定，将不会向用户显示任何内容，0-200个字符
            'show_alert',//如果为真，客户端将在聊天屏幕顶部显示警报，而不是通知。默认为false。
            'url',
            'cache_time'
        ], $conf);
        return $this->curl('answerCallbackQuery', $data);
    }
}