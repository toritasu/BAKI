<?php

header("Content-Type: application/json; charset=UTF-8");

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
  protected $nickname;
  protected $hp;
  protected $img;
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
    return $this->nameshort;
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
  // HPのみセッターも用意
  // セッターを使うことで、直接代入させずにバリデーションチェックを行ってから代入させることができる
  // filter_varは値に対して色々なパターンのバリデーションを行える便利関数
  public function setHp($str){
    $this->hp = filter_var($str, FILTER_VALIDATE_INT);
  }
  // attackメソッド
  public function attack($targetObj,$rate = 1){
    $attackPoint = mt_rand($this->attackMin, $this->attackMax) * $rate;
    History::set($this->getName().'の攻撃ッ!!');

    if(!mt_rand(0,$this->critical)){ // 一定の確率でクリティカル
      $attackPoint = $attackPoint * 2;
      History::set('モロに入ったァ～ッ!!');
    }

    $attackPoint = (int)$attackPoint;
    $targetObj->setHp($targetObj->getHp() - $attackPoint);
    History::set($attackPoint.'のダメージッ!!');
  }
}

// ===============================
// 地下格闘場戦士クラス
// ===============================
class Fighter extends Grappler{
  protected $imgFace;
  protected $tension;
  public function __construct($name, $nameShort, $nickname, $description, $hp, $img, $imgFace, $attackMin, $attackMax, $critical, $tension){
    $this->name = $name;
    $this->nameShort = $nameShort;
    $this->nickname = $nickname;
    $this->description = $description;
    $this->hp = $hp;
    $this->img = $img;
    $this->imgFace = $imgFace;
    $this->attackMin = $attackMin;
    $this->attackMax = $attackMax;
    $this->critical = $critical;
    $this->tension = $tension;
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
}

// ===============================
// 敵キャラクタークラス
// ===============================
class Monster extends Grappler{
  // コンストラクタ
  public function __construct($name, $nameShort, $nickname, $hp, $img, $attackMin, $attackMax) {
    $this->name = $name;
    $this->nameShort = $nameshort;
    $this->nickname = $nickname;
    $this->hp = $hp;
    $this->img = $img;
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
  function __construct($name, $nameShort, $nickname, $hp, $img, $attackMin, $attackMax, $weponAttackMin, $weponAttackMax){
    // 親クラスのコンストラクタで処理する内容を継承したい場合には親コンストラクタを呼び出す
    parent::__construct($name, $nameShort, $nickname, $hp, $img, $attackMin, $attackMax);
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
$fighters[] = new Fighter( '範馬 刃牙', '刃牙', '『地下格闘場チャンピオン』', '数多の死闘をくぐり抜け、齢十八になったばかりの地下闘技場チャンピオン。『地上最強』の父親を越えるべく激闘の日々を続けている。', 500, 'img/fighter01.png', 'img/fighter01_face.png', 30, 70, 3, Tension::MAJI );
$fighters[] = new Fighter( '花山 薫', '花山', '『伝説の喧嘩師』', '15歳にして花山組2代目を襲名した『日本一の喧嘩師』常人離れの握力を誇り、素手喧嘩（ステゴロ）をモットーとしている。', 800, 'img/fighter02.png', 'img/fighter02_face.png', 120, 120, 9, Tension::MAJI );
$fighters[] = new Fighter( '愚地 独歩', '独歩', '『武神』', '実戦空手『神心会』の創始者で、『人食い愚地』の異名を取る。かつてはシベリアトラを素手で屠ったことも。', 450, 'img/fighter03.png', 'img/fighter03_face.png', 30, 80, 4, Tension::MAJI );
$fighters[] = new Fighter( '烈 海王', '烈', '『海王』', '中国武術界における高位の称号『海王』を名に持つ拳雄。4000年を誇る中国拳法の歴史においてNo.1といわれる天才', 600, 'img/fighter04.png', 'img/fighter04_face.png', 40, 80, 5, Tension::BOKE );
$fighters[] = new Fighter( '渋川 剛気', '渋川', '『生きる伝説』', '小柄で老齢ながら『武の体現』の名に恥じない渋川流合気柔術の達人。合気を実戦レベルまで高めたとして生きる伝説とも呼ばれる。', 300, 'img/fighter05.png', 'img/fighter05_face.png', 20, 50, 1.5, Tension::MAJI );

$monsters[] = new WeponMonster( 'ドリアン', 'ドイル', '『卑劣を極めしジェントルマン』', 200, 'img/monster01.png', 15,65, 30,60 );
$monsters[] = new WeponMonster( 'ヘクター・ドイル', 'ドイル', '『麗しき人間凶器』', 120, 'img/monster02.png', 30,35, 5,100 );
$monsters[] = new WeponMonster( 'シコルスキー', 'シコルスキー', '『極寒の地が生んだ究極のクライマー』', 150, 'img/monster03.png', 20,40, 10,20 );
$monsters[] = new WeponMonster( 'スペック', 'スペック', '『呼吸を捨てた外道』', 200, 'img/monster04.png', 40,50, 20,50 );
$monsters[] = new WeponMonster( '柳 流光', '柳', '『人間毒ガス兵器』', 120, 'img/monster05.png', 50,70, 15,40 );
$monsters[] = new Monster( 'ビスケット・オリバ', 'オリバ', '『ミスターアンチェイン』', 240, 'img/monster06.png', 5,100 );
$monsters[] = new Monster( 'ジャック・ハンマー', 'ジャック', '『鬼の血を継ぐ戦士』', 220, 'img/monster07.png', 30,55 );
$monsters[] = new Monster( '範馬勇次郎', '勇次郎', '『地上最強の生物』', 300, 'img/monster08.png', 100,300 );

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
}

// ゲームオーバー時にセッションをリセットする関数
function gameOver(){
  $_SESSION = array();
  error_log('ゲームオーバー');
}

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
