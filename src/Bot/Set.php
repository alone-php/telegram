<?php

namespace AlonePhp\Telegram\Bot;

trait Set{
    /**
     * 接收数据,使用前要删除WebHook
     * 使用此方法通过长轮询接收传入的更新 (wiki)。返回更新对象的数组。
     * https://core.telegram.org/bots/api#getupdates
     * @param int|string $offset
     * @param int        $limit
     * @param int        $timeout
     * @param array      $allowed_updates
     *  message: 普通消息
     *  callback_query: 回调查询（来自按钮点击）
     *  poll: 投票
     *  poll_answer: 投票答案
     *  channel_post: 频道消息
     *  edited_message: 编辑过的消息
     *  edited_channel_post: 编辑过的频道消息
     *  inline_query: 内联查询
     *  chosen_inline_result: 选择的内联结果
     *  shipping_query: 运输查询（用于购物）
     *  pre_checkout_query: 预检查查询（用于购物）
     * @return $this
     */
    public function getUpdates(int|string $offset = 0, int $limit = 100, int $timeout = 0, array $allowed_updates = []): static {
        $data['offset'] = $offset;
        $data['limit'] = $limit;
        $data['timeout'] = $timeout;
        if (!empty($allowed_updates)) {
            $data['allowed_updates'] = is_array($allowed_updates) ? json_encode($allowed_updates) : $allowed_updates;
        }
        return $this->curl('getUpdates', $data);
    }

    /**
     * 获取机器人信息
     * 测试机器人身份验证令牌的简单方法。不需要参数。以 User 对象的形式返回有关机器人的基本信息。
     * https://core.telegram.org/bots/api#getme
     * @return $this
     */
    public function getMe(): static {
        return $this->curl('getMe');
    }

    /**
     * 获取当前的WebHook状态
     * 使用此方法获取当前的 webhook 状态。不需要参数。成功时，返回 WebhookInfo 对象。如果机器人使用 getUpdates，将返回一个 url 字段为空的对象。
     * https://core.telegram.org/bots/api#getwebhookinfo
     * @return $this
     */
    public function getWebhookInfo(): static {
        return $this->curl('getWebhookInfo');
    }

    /**
     * 删除WebHook,切换回getUpdates
     * 使用此方法删除 Webhook 集成。成功则返回 True。
     * https://core.telegram.org/bots/api#deletewebhook
     * @param bool $updates 通过True删除所有待处理的更新
     * @return $this
     */
    public function deleteWebhook(bool $updates = false): static {
        return $this->curl('deleteWebhook', ['drop_pending_updates' => $updates]);
    }

    /**
     * 设置WebHook
     * 使用此方法指定 URL 并通过传出 Webhook 接收传入更新。每当机器人有更新时，我们都会向指定的 URL 发送 HTTPS POST 请求，其中包含 JSON 序列化的更新。如果请求不成功，我们将在合理尝试后放弃。成功则返回 True。
     * https://core.telegram.org/bots/api#setwebhook
     * @param string $url
     * @param array  $pull_type
     * @param string $token
     * @param array  $conf
     * @return $this
     */
    public function setWebhook(string $url, array $pull_type = [], string $token = '', array $conf = []): static {
        $conf['url'] = $url;
        $conf['secret_token'] = $token;
        $conf['allowed_updates'] = $pull_type;
        $data = $this->get_conf([
            'url',//用于发送更新的 HTTPS URL。使用空字符串来删除 webhook 集成
            'certificate',//上传您的公钥证书，以便检查正在使用的根证书。有关详细信息，请参阅我们的自签名指南。
            'ip_address',//用于发送 webhook 请求的固定 IP 地址，而不是通过 DNS 解析的 IP 地址
            'max_connections' => 100,//允许同时与 webhook 建立 HTTPS 连接以进行更新传递的最大数量，1-100。默认为40。使用较低的值可限制机器人服务器上的负载，使用较高的值可增加机器人的吞吐量。
            'allowed_updates',//更新类型
            'drop_pending_updates',//传递True以删除所有待处理的更新
            'secret_token' //在每个 webhook 请求中，在标头“X-Telegram-Bot-Api-Secret-Token”中发送一个秘密令牌，长度为 1-256 个字符。仅允许使用A-Z、a-z、0-9和字符。标头可用于确保请求来自您设置的 webhook。_-
        ], $conf);
        return $this->curl('setWebhook', json_encode($data), 'json');
    }
}