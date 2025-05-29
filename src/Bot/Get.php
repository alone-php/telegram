<?php

namespace AlonePhp\Telegram\Bot;

trait Get{
    /**
     * 获取聊天成员数量
     * 使用此方法获取聊天中的成员数量。成功时返回Int 。
     * @return $this
     */
    public function getChatMemberCount(): static {
        $conf['chat_id'] = $this->chat_id;
        return $this->curl('getChatMemberCount', $conf);
    }

    /**
     * 获取聊天管理员
     * 使用此方法获取聊天中的管理员列表（非机器人）。返回ChatMember对象数组。
     * @return $this
     */
    public function getChatAdministrators(): static {
        return $this->curl('getChatAdministrators', ['chat_id' => $this->chat_id]);
    }

    /**
     * 获取聊天信息
     * 使用此方法获取有关聊天的最新信息。成功时返回ChatFullInfo对象。
     * @return $this
     */
    public function getChat(): static {
        return $this->curl('getChat', ['chat_id' => $this->chat_id]);
    }
}