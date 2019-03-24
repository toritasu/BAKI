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
  public function getNickname(){
    return $this->nickname;
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
  public function __construct($name, $nickname, $hp, $img, $imgFace, $attackMin, $attackMax, $critical, $tension){
    $this->name = $name;
    $this->nickname = $nickname;
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
  public function __construct($name, $nickname, $hp, $img, $attackMin, $attackMax) {
    $this->name = $name;
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
  function __construct($name, $nickname, $hp, $img, $attackMin, $attackMax, $weponAttackMin, $weponAttackMax){
    // 親クラスのコンストラクタで処理する内容を継承したい場合には親コンストラクタを呼び出す
    parent::__construct($name, $nickname, $hp, $img, $attackMin, $attackMax);
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
$fighters[] = new Fighter( '範馬 刃牙', '『地下格闘場チャンピオン』', 500, 'img/fighter01.png', 'img/fighter01_face.png', 30, 70, 3, Tension::MAJI );
$fighters[] = new Fighter( '花山 薫', '『伝説の喧嘩師』', 800, 'img/fighter02.png', 'img/fighter02_face.png', 120, 120, 9, Tension::MAJI );
$fighters[] = new Fighter( '愚地 独歩', '『武神』', 450, 'img/fighter03.png', 'img/fighter03_face.png', 30, 80, 4, Tension::MAJI );
$fighters[] = new Fighter( '烈 海王', '『海王』', 600, 'img/fighter04.png', 'img/fighter04_face.png', 40, 80, 5, Tension::BOKE );
$fighters[] = new Fighter( '渋川 剛気', '『生きる伝説』', 300, 'img/fighter05.png', 'img/fighter05_face.png', 20, 50, 1.5, Tension::MAJI );

$monsters[] = new WeponMonster( 'ドリアン', '『卑劣を極めしジェントルマン』', 200, 'img/monster01.png', 15,65, 30,60 );
$monsters[] = new WeponMonster( 'ドイル', '『麗しき人間凶器』', 120, 'img/monster02.png', 30,35, 5,100 );
$monsters[] = new WeponMonster( 'シコルスキー', '『極寒の地が生んだ究極のクライマー』', 150, 'img/monster03.png', 20,40, 10,20 );
$monsters[] = new WeponMonster( 'スペック', '『呼吸を捨てた外道』', 200, 'img/monster04.png', 40,50, 20,50 );
$monsters[] = new WeponMonster( '柳 流光', '『人間毒ガス兵器』', 120, 'img/monster05.png', 50,70, 15,40 );
$monsters[] = new Monster( 'ビスケット・オリバ', '『ミスターアンチェイン』', 240, 'img/monster06.png', 5,100 );
$monsters[] = new Monster( 'ジャック・ハンマー', '『鬼の血を継ぐ戦士』', 220, 'img/monster07.png', 30,55 );
$monsters[] = new Monster( '範馬勇次郎', '『地上最強の生物』', 300, 'img/monster08.png', 100,300 );

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

?>

<!-- ====================
画面表示
==================== -->
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=deviec-width, initial-scale=1">
    <title>刃牙vs最凶死刑囚</title>
    <link href="https://fonts.googleapis.com/css?family=Noto+Serif+SC:900" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="css/reset.css"> <!--順番を間違えないこと-->
    <link type="text/css" rel="stylesheet" href="css/style.css">
  </head>
  <body>
    <!-- キャラクター選択画面 -->
    <?php if(empty($_SESSION)){ ?>

      <header class="header">
        <img class="logo" src="img/logo.png" alt="刃牙ロゴ">
        <h1 class="title">地下格闘場戦士 VS 最凶死刑囚</h1>
      </header>

      <main class="container">
        <!-- 選択した戦士の詳細を表示するウィンドウ -->
        <section class="view">
          <!-- 初期画面は死刑囚５人のカット -->
          <img class="view-initial" src="img/shikeishu.jpg" alt="最凶死刑囚">
          <!-- 選択したグラップラーの詳細を表示する -->
          <div class="view-selection">
            <div class="character">
              <p class="character-nickname">地下格闘場チャンピオン</p>
              <h2 class="character-name">範馬 刃牙</h2>
              <p class="character-note">
                数多の死闘をくぐり抜け、齢十八になったばかりの地下闘技場チャンピオン<br>
                『地上最強』の父親を越えるべく激闘の日々を続けている。
              </p>
            </div>
            <img class="view-face" src="img/fighter01_face.png" alt="">
          </div>
        </section>

        <section class="container">
          <p class="panel-text">グラップラーを選択するのじゃッ!!</p>
          <form method="post" action="">
            <div class="panel-group">
            <!-- 戦士たちの画像を読み込む -->
            <?php foreach($fighters as $key => $val): ?>
              <label class="panel panel-fighter">
                <input type="radio" name="fighter" value=<?php echo ($val === reset($fighters)) ? $key.' checked="checked"' : $key; ?>>
                <?php echo $val->getName(); ?>
                <img class="panel-image" src="<?php echo $val->getImg(); ?>">
              </label>
            <?php endforeach ?>
            </div>
            <input class="btn btn-inactive" type="submit" name="start" value="肉宴開幕ッ!">
          </form>
        </section>
      </main>

    <!-- バトル画面 -->
    <?php } else { ?>
      <!-- 画面左側 -->
      <div class="left">
        <h2><?php echo $_SESSION['monster']->getNickname().$_SESSION['monster']->getName(); ?>だッ!!</h2>

        <!-- 敵キャラのHPゲージ -->
          <div class="gauge_num"><span><?php echo $_SESSION['monster']->getHp().'/'.$_SESSION['monsterMaxHp']; ?></span></div>
          <div class="gauge_bg"><div class="gauge_main" style="width: 100%;"></div></div>
          <div class="gauge_ttl"><span>HP</span></div>

        <div class="image">
          <img style="width:100%;" src="<?php echo $_SESSION['monster']->getImg(); ?>" style="">
        </div>
      </div>
      <div class="right">
        <div class="history">
          <p><?php echo (!empty($_SESSION['history'])) ? $_SESSION['history'] : ''; ?></p>
        </div>
        <div class="menu" style="margin-top:10px;">
          <p class="parameter">倒した敵：<?php echo $_SESSION['knockDownCount']; ?>人</p>
          <p cloass="parameter">残りの敵：<?php echo $_SESSION['monsterNum']; ?>人</p>
          <img class="fighter_face" src="<?php echo $_SESSION['fighter']->getImgFace(); ?>" alt="">

          <!-- 操作キャラのHPゲージ -->
            <div class="gauge_num"><span><?php echo $_SESSION['fighter']->getHp().'/'.$_SESSION['fighterMaxHp']; ?></span></div>
            <div class="gauge_bg"><div class="gauge_main" style="width: 100%;"></div></div>
            <div class="gauge_ttl"><span>HP</span></div>

          <!-- コマンドエリア -->
          <form class="form" method="post">
            <input class="btn btn-active" type="submit" name="attack" value="攻撃ッ!!">
            <input class="btn btn-active" type="submit" name="guard" value="防御ッ!!">
            <input class="btn btn-active" type="submit" name="escape" value="退却ッ!!">
          </form>
        </div>
      </div>
    <?php } ?>

  <footer class="footer">
    <?php if(!empty($_SESSION)){ ?>
    <form method="post" action="">
      <input class="link_start" type="submit" name="reset" value="<< スタート画面へ">
    </form>
    <?php } ?>
    <a class="link_anime" href="http://baki-anime.jp/" target=”_blank”>TVアニメ「バキ」公式サイトへッ!!</a>
  </footer>
  <script
    src="http://code.jquery.com/jquery-3.3.1.js"
    integrity="sha256-2Kok7MbOyxpgUVvAk/HJ2jigOSYS2auK4Pfzbm7uH60="
    crossorigin="anonymous"></script>
  <script src="app.js"></script>
  </body>
</html>
