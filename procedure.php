<?php
// ===============================
// 画面処理開始
// ===============================
// POST送信された場合
if(!empty($_POST)){
  $startFlg = (!empty($_POST['start'])) ? true : false;
  $fighterFlg = (!is_null($_POST['fighter'])) ? true : false;
  $attackFlg = (!empty($_POST['attack'])) ? true : false;
  $guardFlg = (!empty($_POST['guard'])) ? true : false;
  $escapeFlg = (!empty($_POST['escape'])) ? true : false;
  $clearFlg = (!empty($_POST['clear'])) ? true : false;
  $resetFlg = (!empty($_POST['reset'])) ? true : false;
  error_log('POSTされたッ！');

  // HistoryリセットフラグがONの場合はHistory::clear
  if(!empty($_SESSION['historyReset'])){
    History::clear();
    $_SESSION['historyReset'] = 0;
  }

  // スタート画面から入った場合、プレイヤーキャラの情報を反映
  if($startFlg){
    if($fighterFlg){
      $fighterKey = (int)$_POST['fighter'];
      createFighter($fighterKey);
      createMonster();
      $_SESSION['knockDownCount'] = 0;
    } else { // fighter番号がPOSTされていない場合は不正アクセスなので戻る。
      session_destroy();
      header("Location:".$_SERVER['PHP_SELF']);
    }
  }

  // 攻撃するを押した場合
  if($attackFlg){

    // ランダムで敵キャラに攻撃を与える
    $_SESSION['fighter']->attack($_SESSION['monster']);
    $_SESSION['monster']->sayCry();

    // モンスターのhpが0になったら、別の敵キャラを出現させる
    if($_SESSION['monster']->getHp() <= 0){
      History::set($_SESSION['monster']->getName().'を倒したッ!!<br>');
      // 倒された敵キャラのIDを専用配列に格納する
      $_SESSION['beatenId'][] = $_SESSION['monsterId'];
      error_log('倒された敵のID：'.print_r($_SESSION['beatenId'], true));
      //次の敵キャラを出現させる
      createMonster();
      $_SESSION['knockDownCount'] += 1;
      $_SESSION['historyReset'] = 1;
    } else {
      // 敵キャラが攻撃をする
      $_SESSION['monster']->attack($_SESSION['fighter']);
      $_SESSION['fighter']->sayCry();
    }
    // テキスト消去フラグ
    $_SESSION['historyReset'] = 1;
    // 自分のhpが0以下になったらゲームオーバー
    if($_SESSION['fighter']->getHp() <= 0){
      gameOver();
    }
  }

  // 防御を押した場合
  if($guardFlg){
    $rate = 0.25; // ダメージ率を設定
    // モンスターが攻撃をする　ダメージ率の引数を追加
    $_SESSION['monster']->attack($_SESSION['fighter'], $rate);
    $_SESSION['fighter']->sayCry();
    // テキスト消去フラグ
    $_SESSION['historyReset'] = 1;
    // 自分のhpが0以下になったらゲームオーバー
    if($_SESSION['fighter']->getHp() <= 0){
      gameOver();
    }
  }

  // 逃げるを押した場合
  if($escapeFlg){
    History::set('逃げたッ!!<br>');
    createMonster();
    $_SESSION['historyReset'] = 1;
  }

  // 「スタート画面に戻る」を押した場合
  if($resetFlg){
    $_SESSION = array();
    if (isset($_COOKIE["PHPSESSID"])) {
      setcookie("PHPSESSID", '', time() - 1800, '/');
    }
    session_destroy();
    header("Location:".$_SERVER['PHP_SELF']);
  }

  // 「履歴を消す」を押した場合
  if($clearFlg){
    History::clear();
  }
}
