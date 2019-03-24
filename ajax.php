<?php

header("Content-Type: application/json; charset=UTF-8");

// クラス定義、インスタンス、関数を読み込む
include('object.php');

// =============================
// Ajax通信
// =============================
$receive = filter_input(INPUT_POST, 'key');
$nickName = $fighters[$receive]->getNickname();
$name = $fighters[$receive]->getName();
$description = $fighters[$receive]->getDescription();
$imgSrc = $fighters[$receive]->getImgFace();
$result = array(
  'gen' => '受け取った値は ' . $receive . ' でした。',
  'nickname' => $nickName,
  'name' => $name,
  'imgSrc' => $imgSrc,
  'description' => $description
);
echo json_encode($result);
exit;
