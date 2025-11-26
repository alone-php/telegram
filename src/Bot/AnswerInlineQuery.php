<?php

namespace AlonePhp\Telegram\Bot;

trait AnswerInlineQuery {
    /**
     * 按钮使用 switch_inline_query=>"内容标识"
     * https://core.telegram.org/bots/api#answerinlinequery
     * @param string|int   $inline_id 回复内联id
     * @param array|string $results   二维array
     * @param array        $conf      配置
     * @return $this
     */
    public function answerInlineQuery(string|int $inline_id, array|string $results = [], array $conf = []): static {
        $conf['inline_query_id'] = $inline_id;
        $conf['results'] = is_array($results) ? json_encode($results) : $results;
        $data = $this->get_conf([
            // 唯一标识已回答的查询
            'inline_query_id',
            // JSON序列化的内联查询结果数组 https://core.telegram.org/bots/api#inlinequeryresult
            'results',
            // 内联查询结果在服务器上可能缓存的最高时间（以秒为单位）。默认值为300。
            'cache_time',
            // 传递True，如果结果仅可能被缓存于发送查询的用户端服务器上。默认情况下，结果可能返回给任何发送相同查询的用户。
            'is_personal',
            // 传递客户端在下一个查询中应发送的偏移量，以使用相同的文本接收更多结果。如果没有更多结果或您不支持分页，请传递空字符串。偏移量长度不能超过64字节。
            'next_offset',
            // 描述要在行内查询结果上方显示的按钮的JSON序列化对象
            'button'
        ], $conf);
        return $this->curl('answerInlineQuery', $data);
    }

    /**
     * 内联文本
     * @param string|int $inline_id 回复内联id
     * @param string     $title     内联标题
     * @param string     $text      内联内容
     * @param array      $conf      配置
     * @param array      $config
     * @return $this
     */
    public function answerInlineArticle(string|int $inline_id, string $title, string $text, array $conf = [], array $config = []): static {
        return $this->answerInlineQuery($inline_id, [$this->answerInlineArticleConfig($title, $text, $conf)], $config);
    }

    /**
     * 内联图片+文本
     * @param string|int $inline_id 回复内联id
     * @param string     $photoUrl  图片url
     * @param string     $text      文本
     * @param string     $thumb     内联缩略图
     * @param array      $conf      配置
     * @param array      $config
     * @return $this
     */
    public function answerInlinePhoto(string|int $inline_id, string $photoUrl, string $text = '', string $thumb = "", array $conf = [], array $config = []): static {
        return $this->answerInlineQuery($inline_id, [$this->answerInlinePhotoConfig($photoUrl, $text, $thumb, $conf)], $config);
    }

    /**
     * 内联动图+ 文本
     * @param string|int $inline_id 回复内联id
     * @param string     $gifUrl    动图url
     * @param string     $text      文本
     * @param string     $thumb     内联缩略图
     * @param array      $conf      配置
     * @param array      $config
     * @return $this
     */
    public function answerInlineGif(string|int $inline_id, string $gifUrl, string $text = '', string $thumb = "", array $conf = [], array $config = []): static {
        return $this->answerInlineQuery($inline_id, [$this->answerInlineGifConfig($gifUrl, $text, $thumb, $conf)], $config);
    }

    /**
     * 内联视频+ 文本
     * @param string|int $inline_id 回复内联id
     * @param string     $videoUrl  视频url
     * @param string     $text      文本
     * @param string     $thumb     内联缩略图
     * @param array      $conf      配置
     * @param array      $config
     * @return $this
     */
    public function answerInlineMpeg4Gif(string|int $inline_id, string $videoUrl, string $text = '', string $thumb = "", array $conf = [], array $config = []): static {
        return $this->answerInlineQuery($inline_id, [$this->answerInlineMpeg4GifConfig($videoUrl, $text, $thumb, $conf)], $config);
    }

    /**
     * 内联文本
     * @param string $title
     * @param string $text
     * @param array  $conf
     * @return array
     */
    public function answerInlineArticleConfig(string $title, string $text, array $conf = []): array {
        $conf["type"] = "article";
        $conf["id"] = "article_" . uniqid();
        $conf["title"] = $title;
        $conf["input_message_content"] = [
            // 最终发送的文本内容
            'message_text' => $text,
            // 可选，HTML 或 MarkdownV2
            'parse_mode'   => strtoupper($conf['parse_mode'] ?? 'html'),
        ];
        $conf["reply_markup"] = $this->reply_markup;
        return $this->get_conf([
            // 类型，固定为 article（表示文本结果）
            'type',
            // 每条结果的唯一 ID，1～64 字节
            'id',
            // 在搜索结果列表中显示的标题
            'title',
            // 发送给用户的真正消息内容（必填）
            'input_message_content',
            // 可选，为发送出去的消息附加内联键盘按钮
            'reply_markup',
            // 可选。搜索结果右侧可点击跳转的 URL（与发送内容无关）
            'url',
            // 可选。在搜索结果列表中显示的简短描述
            'description',
            // 可选。搜索结果中的缩略图 URL
            'thumbnail_url',
            // 可选。缩略图宽度
            'thumbnail_width',
            // 可选。缩略图高度
            'thumbnail_height'
        ], $conf);
    }

    /**
     * 内联图片+文本
     * @param string $photoUrl 图片url
     * @param string $text     文本
     * @param string $thumb    内联缩略图
     * @param array  $conf     配置
     * @return array
     */
    public function answerInlinePhotoConfig(string $photoUrl, string $text = '', string $thumb = "", array $conf = []): array {
        $conf["type"] = "photo";
        $conf["id"] = "photo_" . uniqid();
        $conf["photo_url"] = $photoUrl;
        $conf["thumbnail_url"] = $conf['thumbnail_url'] ?? (!empty($thumb) ? $thumb : $photoUrl);
        $conf["parse_mode"] = strtoupper($conf['parse_mode'] ?? 'html');
        $conf["show_caption_above_media"] = $conf['show_caption_above_media'] ?? false;
        $conf["caption"] = $text;
        $conf["reply_markup"] = $this->reply_markup;
        return $this->get_conf([
            // 类型，必须是 photo
            'type',
            // 唯一 ID。长度 1-64 字节
            'id',
            // 图片的真实 URL（必须是 JPG，大小 <= 5MB）
            'photo_url',
            // 缩略图 URL
            'thumbnail_url',
            // 可选：图片宽度
            'photo_width',
            // 可选：图片高度
            'photo_height',
            // 可选：标题
            'title',
            // 可选：简短描述
            'description',
            // 可选：图片发送时附带的文字说明（caption）
            'caption',
            // 可选：caption 的解析模式（Markdown，HTML）
            'parse_mode',
            // 可选：caption entities
            'caption_entities',
            // 可选：caption 是否显示在媒体上方
            'show_caption_above_media',
            // 可选：内联按钮（InlineKeyboardMarkup）
            'reply_markup',
            // 可选：如果不想发送图片，而是发送其它内容
            'input_message_content'
        ], $conf);
    }


    /**
     * 内联动图+ 文本
     * @param string $gifUrl
     * @param string $text  文本
     * @param string $thumb 内联缩略图
     * @param array  $conf  配置
     * @return array
     */
    public function answerInlineGifConfig(string $gifUrl, string $text = '', string $thumb = "", array $conf = []): array {
        $conf["type"] = "gif";
        $conf["id"] = "gif_" . uniqid();
        $conf["gif_url"] = $gifUrl;
        $conf["thumbnail_url"] = $conf['thumbnail_url'] ?? (!empty($thumb) ? $thumb : $gifUrl);
        $conf["parse_mode"] = strtoupper($conf['parse_mode'] ?? 'html');
        $conf["show_caption_above_media"] = $conf['show_caption_above_media'] ?? false;
        $conf["caption"] = $text;
        $conf["reply_markup"] = $this->reply_markup;
        return $this->get_conf([
            // 类型，必须是 gif
            'type',
            // 唯一 ID，1-64 字节
            'id',
            // GIF 文件的真实 URL
            'gif_url',
            // 可选：GIF 宽度
            'gif_width',
            // 可选：GIF 高度
            'gif_height',
            // 可选：GIF 时长（秒）
            'gif_duration',
            // 缩略图 URL（可以是 JPEG、GIF 或 MPEG4）
            'thumbnail_url',
            // 可选：缩略图的 MIME 类型
            // 可选值: image/jpeg, image/gif, video/mp4（默认 image/jpeg）
            'thumbnail_mime_type',
            // 可选：标题
            'title',
            // 可选：GIF 发送时的文本说明
            'caption',
            // 可选：caption 的解析模式（HTML 或 Markdown）
            'parse_mode',
            // 可选：caption entities 列表
            'caption_entities',
            // 可选：caption 是否显示在媒体上方
            'show_caption_above_media',
            // 可选：内联按钮
            'reply_markup',
            // 可选：替代要发送的内容，而不是 GIF
            'input_message_content'
        ], $conf);
    }

    /**
     * 内联视频+ 文本
     * @param string $videoUrl
     * @param string $text  文本
     * @param string $thumb 内联缩略图
     * @param array  $conf  配置
     * @return array
     */
    public function answerInlineMpeg4GifConfig(string $videoUrl, string $text = '', string $thumb = "", array $conf = []): array {
        $conf["type"] = "mpeg4_gif";
        $conf["id"] = "mpeg4_gif_" . uniqid();
        $conf["mpeg4_url"] = $videoUrl;
        $conf["thumbnail_url"] = $conf['thumbnail_url'] ?? (!empty($thumb) ? $thumb : $videoUrl);
        $conf["parse_mode"] = strtoupper($conf['parse_mode'] ?? 'html');
        $conf["show_caption_above_media"] = $conf['show_caption_above_media'] ?? false;
        $conf["caption"] = $text;
        $conf["reply_markup"] = $this->reply_markup;
        return $this->get_conf([
            // 类型，必须是 mpeg4_gif
            'type',
            // 唯一 ID，1~64 字节
            'id',
            // MPEG4 动画文件 URL（H.264 视频，无声音）
            'mpeg4_url',
            // 可选：视频宽度
            'mpeg4_width',
            // 可选：视频高度
            'mpeg4_height',
            // 可选：视频时长（秒）
            'mpeg4_duration',
            // 缩略图 URL，可为 JPG / GIF / MPEG4
            'thumbnail_url',
            // 可选：缩略图 MIME 类型，取值: image/jpeg, image/gif, video/mp4
            // 默认 image/jpeg
            'thumbnail_mime_type',
            // 可选：标题
            'title',
            // 可选：发送该视频时的文字说明
            'caption',
            // 可选：caption 解析模式 (HTML / Markdown)
            'parse_mode',
            // 可选：caption 的实体结构
            'caption_entities',
            // 可选：caption 是否显示在媒体上方
            'show_caption_above_media',
            // 可选：内联按钮
            'reply_markup',
            // 可选：替代要发送的内容，而不是视频动画内容
            'input_message_content'
        ], $conf);
    }
}