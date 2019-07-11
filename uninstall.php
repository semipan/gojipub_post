<?php
/**
 * 卸载脚本，需要卸载数据表或者缓存什么的
 */
$config = json_decode(file_get_contents(CORE_PATH . "/config/site.json"), true);
$file = BASE_PATH . '/db/'.$config['db'];
$sqlLiteDB = new \SQLite3($file);
//删除plu_post
$sql = <<<EOF
DROP TABLE `plu_post`;
EOF;
$ret = $sqlLiteDB->exec($sql);
if (!$ret) {
    throw new \Exception($sqlLiteDB->lastErrorMsg());
}
//删除plu_tag
$sql = <<<EOF
DROP TABLE `plu_tag`;
EOF;
$ret = $sqlLiteDB->exec($sql);
if (!$ret) {
    throw new \Exception($sqlLiteDB->lastErrorMsg());
}
//删除plu_tag_map
$sql = <<<EOF
DROP TABLE `plu_tag_map`;
EOF;
$ret = $sqlLiteDB->exec($sql);
if (!$ret) {
    throw new \Exception($sqlLiteDB->lastErrorMsg());
}
//删除plu_route_map
$sql = <<<EOF
DROP TABLE `plu_route_map`;
EOF;
$ret = $sqlLiteDB->exec($sql);
if (!$ret) {
    throw new \Exception($sqlLiteDB->lastErrorMsg());
}