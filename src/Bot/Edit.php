<?php

namespace AlonePhp\Telegram\Bot;

trait Edit {
    /**
     * 编辑按钮
     * 使用此方法仅编辑消息的回复标记。成功后，如果编辑后的消息不是内联消息，则返回已编辑的消息，否则将返回True。请注意，机器人未发送且不包含内联键盘的业务消息只能在发送后48小时内进行编辑。
     * https://core.telegram.org/bots/api#editmessagereplymarkup
     * @param array $conf
     * @return $this
     */
    public function editMessageReplyMarkup(array $conf = []): static {
        $conf['chat_id'] = $this->chat_id;
        $conf['message_id'] = $this->message_id;
        $data = $this->get_conf([
            'business_connection_id',
            'chat_id',
            'message_id',
            'inline_message_id',
            'reply_markup'
        ], $this->reply_markup($conf));
        return $this->curl('editMessageReplyMarkup', $data);
    }

    /**
     * 编辑文本信息
     * https://core.telegram.org/bots/api#editmessagetext
     * @param string $content
     * @param array  $conf
     * @return $this
     */
    public function editMessageText(string $content, array $conf = []): static {
        $conf['chat_id'] = $this->chat_id;
        $conf['message_id'] = $this->message_id;
        $conf['text'] = $content;
        $data = $this->get_conf([
            'business_connection_id',
            'chat_id',
            'message_id',
            'inline_message_id',
            'text'       => $content,
            'parse_mode' => 'HTML',
            'entities',
            'link_preview_options',
            'reply_markup'
        ], $this->reply_markup($conf));
        return $this->curl('editMessageText', $data);
    }

    /**
     * 编辑 文档+文本
     * @param mixed  $file
     * @param string $content
     * @param array  $conf
     * @param array  $media
     * @return $this
     */
    public function editMessageDocument(mixed $file, string $content = '', array $conf = [], array $media = []): static {
        $media['media'] = $file;
        $media['caption'] = $content;
        $medias = $this->get_conf([
            'type'       => 'document',
            'media',
            'thumbnail',
            'caption',
            'parse_mode' => 'HTML',
            'caption_entities',
            'disable_content_type_detection'
        ], $media);
        return $this->editMessageMedia($conf, $medias);
    }

    /**
     * 编辑 音频+文本
     * @param mixed  $file
     * @param string $content
     * @param array  $conf
     * @param array  $media
     * @return $this
     */
    public function editMessageAudio(mixed $file, string $content = '', array $conf = [], array $media = []): static {
        $media['media'] = $file;
        $media['caption'] = $content;
        $medias = $this->get_conf([
            'type'       => 'audio',
            'media',
            'thumbnail',
            'caption',
            'parse_mode' => 'HTML',
            'caption_entities',
            'duration',
            'performer',
            'title'
        ], $media);
        return $this->editMessageMedia($conf, $medias);
    }

    /**
     * 编辑 动画+文本
     * @param mixed  $file
     * @param string $content
     * @param array  $conf
     * @param array  $media
     * @return $this
     */
    public function editMessageAnimation(mixed $file, string $content = '', array $conf = [], array $media = []): static {
        $media['media'] = $file;
        $media['caption'] = $content;
        $medias = $this->get_conf([
            'type'       => 'animation',
            'media',
            'thumbnail',
            'caption',
            'parse_mode' => 'HTML',
            'caption_entities',
            'show_caption_above_media',
            'width',
            'height',
            'duration',
            'has_spoiler'
        ], $media);
        return $this->editMessageMedia($conf, $medias);
    }

    /**
     * 编辑 视频+文本
     * @param mixed  $file
     * @param string $content
     * @param array  $conf
     * @param array  $media
     * @return $this
     */
    public function editMessageVideo(mixed $file, string $content = '', array $conf = [], array $media = []): static {
        $media['media'] = $file;
        $media['caption'] = $content;
        $medias = $this->get_conf([
            'type'       => 'video',
            'media',
            'thumbnail',
            'caption',
            'parse_mode' => 'HTML',
            'caption_entities',
            'show_caption_above_media',
            'width',
            'height',
            'duration',
            'supports_streaming',
            'has_spoiler'
        ], $media);
        return $this->editMessageMedia($conf, $medias);
    }

    /**
     * 编辑 图片+文本
     * https://core.telegram.org/bots/api#inputmediaphoto
     * @param mixed  $file
     * @param string $content
     * @param array  $conf
     * @param array  $media
     * @return $this
     */
    public function editMessagePhoto(mixed $file, string $content = '', array $conf = [], array $media = []): static {
        $media['media'] = $file;
        $media['caption'] = $content;
        $medias = $this->get_conf([
            'type'       => 'photo',
            'media',
            'caption',
            'parse_mode' => 'HTML',
            'caption_entities',
            'show_caption_above_media',
            'has_spoiler'
        ], $media);
        return $this->editMessageMedia($conf, $medias);
    }

    /**
     * 编辑消息媒体
     * https://core.telegram.org/bots/api#editmessagemedia
     * @param array $conf
     * @param array $media
     * @return $this
     */
    public function editMessageMedia(array $conf = [], array $media = []): static {
        $conf['chat_id'] = $this->chat_id;
        $conf['message_id'] = $this->message_id;
        $conf['media'] = $media;
        $data = $this->get_conf([
            'business_connection_id',
            'chat_id',
            'message_id',
            'inline_message_id',
            'media',
            'reply_markup'
        ], $this->reply_markup($conf));
        return $this->curl('editMessageMedia', json_encode($data), true);
    }

    /**
     * 删除信息
     * https://core.telegram.org/bots/api#deletemessage
     * https://core.telegram.org/bots/api#deletemessages
     * @param array $message_id 批量删除
     * @return $this
     */
    public function deleteMessage(array $message_id = []): static {
        $data['chat_id'] = $this->chat_id;
        if (!empty($message_id)) {
            $data['message_ids'] = json_encode($message_id);
        } else {
            $data['message_id'] = $this->message_id;
        }
        return $this->curl('deleteMessage', $data);
    }

    /**
     * 置顶消息
     * 使用此方法将消息添加到聊天中的固定消息列表中。如果聊天不是私人聊天，则机器人必须是聊天中的管理员才能正常工作，并且必须在超级组中拥有“can_pin_messages”管理员或“can_edit_messages”管理员在频道中。成功后返回真实。
     * https://core.telegram.org/bots/api#pinchatmessage
     * @param bool $disable_notification
     * @return $this
     */
    public function pinChatMessage(bool $disable_notification = true): static {
        $data['chat_id'] = $this->chat_id;
        $data['message_id'] = $this->message_id;
        $data['disable_notification'] = $disable_notification;
        return $this->curl('pinChatMessage', $data);
    }

    /**
     * 取消置顶
     * https://core.telegram.org/bots/api#unpinchatmessage
     * @return $this
     */
    public function unpinChatMessage(): static {
        $data['chat_id'] = $this->chat_id;
        $data['message_id'] = $this->message_id;
        return $this->curl('unpinChatMessage', $data);
    }

    /**
     * 取消全部置顶
     * https://core.telegram.org/bots/api#unpinallchatmessages
     * @return $this
     */
    public function unpinAllChatMessages(): static {
        $data['chat_id'] = $this->chat_id;
        return $this->curl('unpinAllChatMessages', $data);
    }
}