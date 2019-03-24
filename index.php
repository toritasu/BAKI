<!-- クラス定義、インスタンス、関数を読み込む -->
<?php include('object.php'); ?>

<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
          <img class="view-initial js-view-initial" src="img/shikeishu.jpg" alt="最凶死刑囚">
          <!-- 選択したグラップラーの詳細を表示する -->
          <div class="view-selection js-view-character">
            <div class="character">
              <p class="character-nickname js-character-nickname"><?php echo $fighters[0]->getNickname(); ?></p>
              <h2 class="character-name js-character-name"><?php echo $fighters[0]->getName(); ?></h2>
              <p class="character-note js-character-description">
                数多の死闘をくぐり抜け、齢十八になったばかりの地下闘技場チャンピオン。<br>
                『地上最強』の父親を越えるべく激闘の日々を続けている。
              </p>
            </div>
            <img class="character-face js-character-face" src="<?php echo $fighters[0]->getImgFace(); ?>" alt="">
          </div>
        </section>

        <section class="container">
          <p class="panel-text">グラップラーを選択するのじゃッ!!</p>
          <form method="post" action="">
            <div class="panel-group">
            <!-- 戦士たちの画像を読み込む -->
            <?php foreach($fighters as $key => $val): ?>
              <label class="panel panel-fighter">
                <input type="radio" name="fighter" value=<?php echo $key; ?>>
                <img class="panel-image" src="<?php echo $val->getImg(); ?>">
              </label>
            <?php endforeach ?>
            </div>
            <input class="btn btn-inactive js-btn-prohibit" type="submit" name="start" value="選べッ!!" disabled="disabled">
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
  <script src="js/app.js"></script>
  </body>
</html>
