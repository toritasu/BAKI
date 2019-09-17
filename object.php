<?php
ini_set('log_errors', 'on'); // ログを取るか
ini_set('error_log', 'php.log'); // ログの出力ファイルを指定
session_start(); // セッションを使う

// モンスターを格納する配列を宣言
$monsters = array();

// テンションのクラス定数
class Tension{
  const MAJI = 1;
  const BOKE = 2;
}
// ===============================
// 抽象クラス（グラップラークラス）
// ===============================
abstract class Grappler{
  // プロパティ
  protected $name;
  protected $nameShort;
  protected $nickname;
  protected $hp;
  protected $mp;
  protected $img;
  protected $imgFace;
  protected $attackMin;
  protected $attackMax;
  protected $critical;
  // 叫び声メソッド（オーバーライドして使う）
  abstract public function sayCry();
  // ゲッター
  public function getName(){
    return $this->name;
  }
  public function getNameShort(){
    return $this->nameShort;
  }
  public function getNickname(){
    return $this->nickname;
  }
  public function getDescription(){
    return nl2br($this->description);
  }
  public function getImg(){
    return $this->img;
  }
  public function getImgFace(){
    return $this->imgFace;
  }
  public function getHp(){
    return $this->hp;
  }
  public function getMp(){
    return $this->mp;
  }
  public function getLethal(){
    return $this->lethal;
  }
  public function getLethalImg(){
    return $this->lethalImg;
  }
  // HPのみセッターも用意
  // セッターを使うことで、直接代入させずにバリデーションチェックを行ってから代入させることができる
  // filter_varは値に対して色々なパターンのバリデーションを行える便利関数
  public function setHp($str){
    $this->hp = filter_var($str, FILTER_VALIDATE_INT);
  }
  public function setMp($str){
    $num = filter_var($str, FILTER_VALIDATE_INT);
    if($num < 100){
      $this->mp = $num;
    } else {
      $this->mp = 100;
    }
  }
  // attackメソッド
  public function attack($targetObj, $rate = 1){
    $attackPoint = mt_rand($this->attackMin, $this->attackMax) * $rate;
    History::set($this->getNameShort().'の攻撃ッ!!');

    if(!mt_rand(0,$this->critical)){ // 一定の確率でクリティカル
      $attackPoint = $attackPoint * 2;
      History::set('モロに入ったァ～ッ!!');
    }

    $attackPoint = (int)$attackPoint;
    $targetObj->setHp($targetObj->getHp() - $attackPoint);
    // ダメージに応じて気力ゲージアップ
    $mpPoint = intval($attackPoint / 3);
    $targetObj->setMp($targetObj->getMp() + $mpPoint);

    History::set($attackPoint.'のダメージッ!!');
  }
}

// ===============================
// 地下格闘場戦士クラス
// ===============================
class Fighter extends Grappler{
  protected $imgFace;
  protected $tension;
  public function __construct($name, $nameShort, $nickname, $description, $hp, $img, $imgFace, $attackMin, $attackMax, $critical, $tension, $lethal, $lethalImg, $lethalAttack){
    $this->name = $name;
    $this->nameShort = $nameShort;
    $this->nickname = $nickname;
    $this->description = $description;
    $this->hp = $hp;
    $this->mp = 0;
    $this->img = $img;
    $this->imgFace = $imgFace;
    $this->attackMin = $attackMin;
    $this->attackMax = $attackMax;
    $this->critical = $critical;
    $this->tension = $tension;
    $this->lethal = $lethal;
    $this->lethalImg = $lethalImg;
    $this->lethalAttack = $lethalAttack;
  }
  public function sayCry(){
    switch($this->tension){
      case Tension::MAJI :
        History::set($this->name.'「くッ!!」');
        break;
      case Tension::BOKE :
        History::set($this->name.'「救命阿（ジュウミンア）ッ!!」');
        break;
    }
  }
  // 必殺技メソッド
  public function lethal($targetObj){
    $lethalAttackMin = $this->lethalAttack*0.5;
    $lethalAttackMax = $this->lethalAttack*1.5;
    $lethalAttackPoint = mt_rand($lethalAttackMin, $lethalAttackMax);
    $lethalAttackPoint = (int)$lethalAttackPoint;

    History::set($this->getNameShort().'の'.$this->getLethal().'ッ!!');
    $targetObj->setHp($targetObj->getHp()-$lethalAttackPoint);
    History::set('決まったァ～ッ!! '.$lethalAttackPoint.'のダメージ!!');

    $_SESSION['fighter']->setMp(0);
    $_SESSION['lethalFlg'] = true;
  }
}

// ===============================
// 敵キャラクタークラス
// ===============================
class Monster extends Grappler{
  // コンストラクタ
  public function __construct($name, $nameShort, $nickname, $hp, $img, $imgFace, $attackMin, $attackMax) {
    $this->name = $name;
    $this->nameShort = $nameShort;
    $this->nickname = $nickname;
    $this->hp = $hp;
    $this->img = $img;
    $this->imgFace = $imgFace;
    $this->attackMin = $attackMin;
    $this->attackMax = $attackMax;
    $this->critical = 9;
  }
  public function sayCry(){
    History::set($this->name.'「ガハッ!!」');
  }
}
// ===============================
// 凶器を使用する敵キャラクタークラス
// ===============================
class WeponMonster extends Monster{
  private $weponAttackMin;
  private $weponAttackMax;
  function __construct($name, $nameShort, $nickname, $hp, $img, $imgFace, $attackMin, $attackMax, $weponAttackMin, $weponAttackMax){
    // 親クラスのコンストラクタで処理する内容を継承したい場合には親コンストラクタを呼び出す
    parent::__construct($name, $nameShort, $nickname, $hp, $img, $imgFace, $attackMin, $attackMax);
    $this->weponAttackMin = $weponAttackMin;
    $this->weponAttackMax = $weponAttackMax;
  }
  // attackメソッドをオーバーライドすることで、「ゲーム進行を管理する処理側」は単にattackメソッドを呼べばいいだけになる
  // 魔法を使えるモンスターは、魔法を出すか否かを「自分で」判断する
  public function attack($targetObj, $rate = 1){
    $weponAttackPoint = mt_rand($this->weponAttackMin, $this->weponAttackMax) * $rate;
    $weponAttackPoint = (int)$weponAttackPoint;
    if(!mt_rand(0,2)){ //3分の1の確率で凶器攻撃
      History::set($this->name.'は卑怯にも凶器を使用したッ!!');
      $targetObj->setHp($targetObj->getHp()-$weponAttackPoint);
      History::set($weponAttackPoint.'のダメージ!!');
      $mpPoint = intval($weponAttackPoint / 2);
      $targetObj->setMp($targetObj->getMp() + $mpPoint);
    }else{
      // 通常の攻撃の場合は、親クラスの攻撃メソッドを呼び出す。
      // こうすれば、親クラスの攻撃メソッドが修正されても反映される。
      parent::attack($targetObj, $rate);
    }
  }
}
// Historyクラスに実装（継承）するインターフェイス
// 「必ず存在しなけらばならないメソッド」を強制するためのもの
interface HistoryInterface{
  public function set($str);
  public function clear();
}
// ===============================
// 履歴管理クラス（インスタンス化して複数に増殖させる必要のないクラスなので、staticにする）
// ===============================
class History implements HistoryInterface{
  public function set($str){
    // セッションhistoryが作られていなければ作る
    if(empty($_SESSION['history'])) $_SESSION['history'] = '';
    // 文字列をセッションhistoryへ格納
    $_SESSION['history'] .= $str.'<br>';
  }
  public function clear(){
    unset($_SESSION['history']);
  }
}

// インスタンス生成
$fighters[] = new Fighter( '範馬 刃牙', '刃牙', '地下格闘場チャンピオン', '数多の死闘をくぐり抜け、齢十八になったばかりの地下闘技場チャンピオン。地上最強の父親を越えるべく激闘の日々を続けている。', 500, 'img/fighter01.png', 'img/fighter01_face.png', 30, 100, 3, Tension::MAJI, '闘いとセックスは似ているッ!!', 'img/fighter01_lethal.jpg', 300);
$fighters[] = new Fighter( '花山 薫', '花山', '伝説の喧嘩師', '15歳にして花山組2代目を襲名した日本一の喧嘩師常人離れの握力を誇り、素手喧嘩（ステゴロ）をモットーとしている。', 550, 'img/fighter02.png', 'img/fighter02_face.png', 0, 200, 7, Tension::MAJI, '握撃', 'img/fighter02_lethal_2.jpg', 250);
$fighters[] = new Fighter( '愚地 独歩', '独歩', '武神', '実戦空手神心会の創始者で、『人食い愚地』の異名を取る。かつてはシベリアトラを素手で屠ったことも。', 450, 'img/fighter03.png', 'img/fighter03_face.png', 40, 80, 4, Tension::MAJI, 'ないほうの手で殴ったァーーッ', 'img/fighter03_lethal.jpg', 300);
$fighters[] = new Fighter( '烈 海王', '烈', '中国4000年が誇る拳雄', '中国武術界における高位の称号海王を名に持つ拳雄。4000年を誇る中国拳法の歴史においてNo.1といわれる天才。', 500, 'img/fighter04.png', 'img/fighter04_face.png', 60, 100, 5, Tension::BOKE, '救命阿（ジュウミンア）', 'img/fighter04_lethal.jpg', 0);
$fighters[] = new Fighter( '渋川 剛気', '渋川', '生きる伝説', '小柄で老齢ながら武の体現の名に恥じない渋川流合気柔術の達人。合気を実戦レベルまで高めたとして、生きる伝説とも呼ばれる。', 280, 'img/fighter05.png', 'img/fighter05_face.png', 40, 80, 1.5, Tension::MAJI, '真の護身', 'img/fighter05_lethal.jpg', 5000);

$monsters[] = new WeponMonster( 'ドリアン', 'ドリアン', '卑劣を極めしジェントルマン', 400, 'img/monster01.png', 'img/monster01_face.png', 40,90, 30,60 );
$monsters[] = new WeponMonster( 'ドイル', 'ドイル', '麗しき人間凶器', 280, 'img/monster02.png', 'img/monster02_face.png', 30,40, 30,200 );
$monsters[] = new WeponMonster( 'シコルスキー', 'シコルスキー', '極寒の地が生んだ究極のクライマー', 300, 'img/monster03.png', 'img/monster03_face.png', 30,70, 10,20 );
$monsters[] = new WeponMonster( 'スペック', 'スペック', '呼吸を捨てた外道', 400, 'img/monster04.png', 'img/monster04_face.png', 50,80, 20,50 );
$monsters[] = new WeponMonster( '柳 流光', '柳', '人間毒ガス兵器', 240, 'img/monster05.png', 'img/monster05_face.png', 50,70, 30,70 );
$monsters[] = new Monster( 'ビスケット・オリバ', 'オリバ', 'ミスターアンチェイン', 480, 'img/monster06.png', 'img/monster06_face.png', 20,100 );
$monsters[] = new Monster( 'ジャック・ハンマー', 'ジャック', '鬼の血を継ぐ戦士', 440, 'img/monster07.png', 'img/monster07_face.png', 50,90 );
$monsters[] = new Monster( '範馬勇次郎', '勇次郎', '地上最強の生物', 700, 'img/monster08.png', 'img/monster08_face.png', 50,150 );

// 地下格闘場戦士を生成する関数
function createFighter($key){
  global $fighters;
  $fighter = $fighters[$key];
  $_SESSION['fighter'] = $fighter;
  $_SESSION['fighterMaxHp'] = $_SESSION['fighter']->getHp();
  error_log('選択したキャラ：'.print_r($fighter,true));
}

// モンスターを生成する関数
function createMonster(){
  // 1. グローバル変数であるインスタンスを呼び出す
  global $monsters;
  // 2. インスタンスの配列から敗北を知った者を削除していく
  foreach($_SESSION['beatenId'] as $key => $val){
    array_splice($monsters, $val, 1);
  }
  // 3. 敵が全滅したらセッションを空にしてスタート画面に戻る
  if(empty($monsters)){
    $_SESSION = array();
    $_SESSION['clear'] = true;
    header("Location:".$_SERVER['PHP_SELF']);
  }
  // 4. 残っている敵キャラからランダムでキーを選択し、敵キャラを呼び出す
  $monsterId = mt_rand(0, count($monsters)-1);
  $monster = $monsters[$monsterId];
  History::set($monster->getName().'と出会ってしまったッ!!');
  // 5. 敵キャラの情報とIDをセッション変数に渡す
  $_SESSION['monster'] = $monster;
  $_SESSION['monsterId'] = $monsterId;
  $_SESSION['monsterNum'] = count($monsters);
  $_SESSION['monsterMaxHp'] = $_SESSION['monster']->getHp();
  error_log('登場した敵キャラ：'.print_r($monster,true));
  // 6. エンカウント用フラグを立てる（後でgetSessionFlashを使って初期化する）
  $_SESSION['encountFlg'] = true;
  // 7. 背景画像をランダムで変更
  $_SESSION['stageImg'] = mt_rand(1, 6);
}

// HP回復
function recoverHp($monsterHp, $maxHp){
  $recovery = mt_rand($monsterHp*.3, $monsterHp*.8);
  History::set('体力が'.$recovery.'回復したッ!!');
  $recoveredHp = $_SESSION['fighter']->getHp() + $recovery;
  if($recoveredHp < $maxHp){
    $_SESSION['fighter']->setHp($recoveredHp);
  } else {
    $_SESSION['fighter']->setHp($maxHp);
  }
}

// ゲームオーバー時にセッションをリセットする関数
function gameOver(){
  $_SESSION = array();
  $_SESSION['gameover'] = true;
  error_log('ゲームオーバー');
}

// セッションを１回だけ取得できる
function getSessionFlash($key){
  if(!empty($_SESSION[$key])){
    $data = $_SESSION[$key];
    $_SESSION[$key] = '';
    return $data;
  }
}
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
  $lethalFlg = (!empty($_POST['lethal'])) ? true : false;
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

// 攻撃または必殺を選んだ場合
  if($attackFlg || $lethalFlg){
    // 攻撃を押した場合
    if($attackFlg){
      $_SESSION['fighter']->attack($_SESSION['monster']);

    // 必殺技を押した場合
    }elseif($lethalFlg){
      $_SESSION['fighter']->lethal($_SESSION['monster']);
      $_SESSION['monster']->sayCry();
    }
    // モンスターのhpが0になったら、別の敵キャラを出現させる
    if($_SESSION['monster']->getHp() <= 0){
      History::set($_SESSION['monster']->getName().'を倒したッ!!<br>');
      // 倒された敵キャラのIDを専用配列に格納する
      $_SESSION['beatenId'][] = $_SESSION['monsterId'];
      error_log('倒された敵のID：'.print_r($_SESSION['beatenId'], true));
      // HPを回復
      recoverHp($_SESSION['monsterMaxHp'], $_SESSION['fighterMaxHp']);
      //次の敵キャラを出現させる
      createMonster();
      $_SESSION['knockDownCount'] += 1;
      $_SESSION['historyReset'] = 1;
    } else {
      // 敵キャラが攻撃をする
      $_SESSION['monster']->attack($_SESSION['fighter']);
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
    error_log('スタート画面に戻ります');
    $_SESSION = array();
    // クッキーが生成されていたら削除する（有効期限を現在時刻より前にする）
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
