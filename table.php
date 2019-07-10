<?php
$config = json_decode(file_get_contents(CORE_PATH . "/config/site.json"), true);
$file = BASE_PATH . '/db/'.$config['db'];
$sqlLiteDB = new \SQLite3($file);
//创建插件-文章的相关表plu_post
$sql = <<<EOF
CREATE TABLE IF NOT EXISTS "plu_post" (
    "id"	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
    "uid"	INTEGER NOT NULL,
    "title"	TEXT NOT NULL,
    "description"	TEXT,
    "tag"	TEXT,
    "draft"	TEXT,
    "text"	TEXT NOT NULL,
    "status"	INTEGER NOT NULL,
    "created_at"	INTEGER NOT NULL,
    "modify_at"	INTEGER
)
EOF;
$ret = $sqlLiteDB->exec($sql);
if (!$ret) {
    throw new \Exception($sqlLiteDB->lastErrorMsg());
}
$tag = 'a:2:{i:0;s:6:"体育";i:1;s:12:"中国女足";}';
$content = <<<EOF
&lt;center&gt;![老贾哭了](/assets/sample.png)&lt;/center&gt;

&lt;center&gt;&lt;small&gt;女足晋级16强的那一刻，老贾哭了&lt;/small&gt;&lt;/center&gt;

几乎是在终场哨响的瞬间，老贾就已经情难自已了。当队员们手牵手走到场边向他鞠躬致谢，曾经在中国足坛以硬汉著称的贾秀全已经无法抑制夺眶而出的泪水。

他原本不想让队员看到自己的眼泪，一边擦拭，一边挥手示意让她们赶紧回到休息室。姑娘们陆续上前小心翼翼地用拥抱给他安慰，老贾不得不抓起衣襟擦去更多的眼泪……

其实队员在赛后发现老贾红了眼圈，第一感觉略显诧异。一场艰难的平局而已，也不是最后一场比赛，哭什么？但其实她们也很清楚这突如其来的泪水而何而来，只不过这是她们第一次看见老贾的眼泪。在此之前，她们甚至都没想过老贾也会流泪。

“主要是看到贾导哭，以前从来没见他哭过。感觉他平时训练里是一个很严谨的人，对我们要求也很高，因为他想我们能更好，就像父亲一样。看到他这样，感觉心里很感动也很难过，他特别不容易，一直带着我们，也帮我们扛住所有压力。所以我也跟着哭了。”获得本场比赛最佳球员的中国女足门将彭诗梦说。
EOF;
$sql = "INSERT INTO plu_post (`uid`, `title`, `description`, `tag`, `text`, `status`, `created_at`) VALUES ('1', '写在中国女足出线之后：老贾的眼泪和他最后的勇气', '几乎是在终场哨响的瞬间，老贾就已经情难自已了。当队员们手牵手走到场边向他鞠躬致谢，曾经在中国足坛以硬汉著称的贾秀全已经无法抑制夺眶而出的泪水。','" . $tag . "','" . $content . "','1'," . time() . ")";
$ret = $sqlLiteDB->exec($sql);
if (!$ret) {
    throw new \Exception($sqlLiteDB->lastErrorMsg());
}
//创建插件-文章的相关表plu_tag
$sql = <<<EOF
CREATE TABLE IF NOT EXISTS "plu_tag" (
    "id"	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
    "name"	TEXT NOT NULL UNIQUE,
    "created_at"	INTEGER NOT NULL
)
EOF;
$ret = $sqlLiteDB->exec($sql);
if (!$ret) {
    throw new \Exception($sqlLiteDB->lastErrorMsg());
}
$sql = "INSERT INTO plu_tag (`name`, `created_at`) VALUES ('体育'," . time() . ")";
$ret = $sqlLiteDB->exec($sql);
if (!$ret) {
    throw new \Exception($sqlLiteDB->lastErrorMsg());
}
$sql = "INSERT INTO plu_tag (`name`, `created_at`) VALUES ('中国女足'," . time() . ")";
$ret = $sqlLiteDB->exec($sql);
if (!$ret) {
    throw new \Exception($sqlLiteDB->lastErrorMsg());
}
//创建插件-文章的相关表plu_tag_map
$sql = <<<EOF
CREATE TABLE IF NOT EXISTS "plu_tag_map" (
    "id"	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
    "tag_id"	INTEGER NOT NULL,
    "post_id"	INTEGER NOT NULL,
    "created_at"	INTEGER NOT NULL
)
EOF;
$ret = $sqlLiteDB->exec($sql);
if (!$ret) {
    throw new \Exception($sqlLiteDB->lastErrorMsg());
}
$sql = "INSERT INTO plu_tag_map (`tag_id`, `post_id`, `created_at`) VALUES ('1','1'," . time() . ")";
$ret = $sqlLiteDB->exec($sql);
if (!$ret) {
    throw new \Exception($sqlLiteDB->lastErrorMsg());
}
$sql = "INSERT INTO plu_tag_map (`tag_id`, `post_id`, `created_at`) VALUES ('2','1'," . time() . ")";
$ret = $sqlLiteDB->exec($sql);
if (!$ret) {
    throw new \Exception($sqlLiteDB->lastErrorMsg());
}
//创建插件-文章的相关表plu_route_map
$sql = <<<EOF
CREATE TABLE IF NOT EXISTS "plu_route_map" (
    "id"	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
    "url"	TEXT NOT NULL,
    "post_id"	INTEGER NOT NULL,
    "created_at"	INTEGER NOT NULL,
    "modify_at"	INTEGER
)
EOF;
$ret = $sqlLiteDB->exec($sql);
if (!$ret) {
    throw new \Exception($sqlLiteDB->lastErrorMsg());
}
$sql = "INSERT INTO plu_route_map (`url`, `post_id`, `created_at`) VALUES ('Chinese-Womens-Football-Team-Advanced-to-the-Top-16-in-2019-French-Womens-Football-World-Cup','1'," . time() . ")";
$ret = $sqlLiteDB->exec($sql);
if (!$ret) {
    throw new \Exception($sqlLiteDB->lastErrorMsg());
}
