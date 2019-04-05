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
    <!-- gameover変数があればゲームオーバー画面へ -->
    <?php if($_SESSION['gameover'] || $_SESSION['clear']){ ?>
      <main class="container">
        <header class="header">
          <img class="header-logo" src="img/logo.png" alt="刃牙ロゴ">
          <h1 class="header-title">地下格闘場戦士 VS 最凶死刑囚</h1>
        </header>
      <?php if($_SESSION['gameover']) { ?>
        <img src="img/gameover.jpg" style="width:100%;">
      <?php }elseif($_SESSION['clear']) { ?>
    <!--clear変数があればゲームクリア画面へ -->
        <img src="img/clear.jpg" style="width:100%;">
        <p class="congratulations">
          遊んでくれてありがとう！<br>
          このパスワードをDMしてくれた方に先着１名でAmazonギフト券1000円分をプレゼント！<br><br>
          5n7i9vgidx92
        </p>
    <?php $_SESSION = array(); } ?>

      <!-- セッション変数が空ならキャラクター選択画面へ -->
    <?php }elseif(empty($_SESSION)){ ?>
      <!-- ローディング画面 -->
      <div class="fade">
        <div class="loader">
          <img src="img/loading.gif" alt="Now Loading..." width="80px" height="80px">
        </div>
      </div>
      <main class="container" style="display:none">
        <header class="header">
          <img class="header-logo" src="img/logo.png" alt="刃牙ロゴ">
          <h1 class="header-title">地下格闘場戦士 VS 最凶死刑囚</h1>
        </header>
        <!-- 選択した戦士の詳細を表示するウィンドウ -->
        <section class="view">
          <!-- 初期画面は死刑囚５人のカット -->
          <div class="view-initial js-view-initial">
            <h2 class="message">「敗北を知りたい」</h2>
            <img class="image" src="img/shikeishu.jpg" alt="最凶死刑囚">
          </div>
          <!-- 選択したグラップラーの詳細を表示する -->
          <div class="view-selection js-view-character">
            <div class="character">
              <p class="character-nickname js-character-nickname"></p>
              <h2 class="character-name js-character-name"></h2>
              <p class="character-note js-character-description"></p>
            </div>
            <img class="character-face js-character-face" src="" alt="">
          </div>
        </section>

        <section class="selection">
          <h2 class="panel-title">東京にどえらい連中が上陸するッ!!</h2>
          <p class="panel-text">グラップラーを選択するのじゃッ!!</p>
          <form method="post" action="">
            <div class="panel-group">
            <!-- 戦士たちの画像を読み込む -->
            <?php foreach($fighters as $key => $val): ?>
              <label class="panel panel-fighter js-panel-bg">
                <input type="radio" name="fighter" value=<?php echo $key; ?>>
                <img class="panel-image" src="<?php echo $val->getImg(); ?>">
              </label>
            <?php endforeach ?>
            </div>
            <input class="btn btn-start btn-inactive js-btn-prohibit" type="submit" name="start" value="選べッ!!" disabled="disabled">
          </form>
        </section>

    <?php } else { ?>
      <!-- セッション変数があればバトル画面へ -->
      <main class="container">

        <!-- 0-1. 敵カットイン -->
        <section class="cutin <?php if(getSessionFlash('encountFlg')) echo 'show'; ?>">
          <div class="cutin-window enemy">
            <img class="img" src="<?php echo $_SESSION['monster']->getImgFace(); ?>" alt="<?php echo $_SESSION['monster']->getName(); ?>">
            <div class="telop">
              <p class="nickname"><?php echo $_SESSION['monster']->getNickname(); ?></p>
              <h2 class="name"><?php echo $_SESSION['monster']->getName(); ?></h2>
            </div>
          </div>
        </section>
        <!-- 0-2. 必殺技カットイン -->
        <section class="cutin js-lethal-cutin">
          <div class="cutin-window lethal">
            <img class="img" src="<?php echo $_SESSION['fighter']->getLethalImg(); ?>" alt="<?php echo $_SESSION['fighter']->getLethal(); ?>">
            <div class="telop">
              <h2 class="name"><?php echo $_SESSION['fighter']->getLethal(); ?></h2>
            </div>
          </div>
        </section>

        <!-- 1. 敵キャラの情報とヒストリーウィンドウ -->
        <section class="battle-window stage-<?php echo $_SESSION['stageImg'] ?>">
          <!-- 敵キャラのHPゲージ -->
          <div class="enemy-info">
            <div class="name"><?php echo $_SESSION['monster']->getName(); ?></div>
            <div class="hp">
              <span id="js-hp-remain-enemy"><?php echo $_SESSION['monster']->getHp(); ?></span>/<span id="js-hp-max-enemy"><?php echo $_SESSION['monsterMaxHp']; ?></span>
            </div>
          </div>
          <div class="gauge">
            <div class="gauge-remain gauge-remain-hp js-gauge-hp-enemy"></div>
          </div>
          <!-- 敵キャラの画像 -->
          <img class="enemy-img" src="<?php echo $_SESSION['monster']->getImg(); ?>" style="">
          <!-- 攻撃エフェクト -->
          <div class="effect">
            <img class="effect-attack left" src="img/attack.png">
          </div>
          <!-- History -->
          <div class="history js-auto-scroll">
            <p class="history-text"><?php echo (!empty($_SESSION['history'])) ? $_SESSION['history'] : ''; ?></p>
          </div>
        </section>

        <!-- 2. 操作キャラのステータス -->
        <section class="fighter-info">
          <!-- 操作キャラの画像 -->
          <div class="fighter-info-face js-fighter-info-face">
            <form class="btn-command-wrapper" method="post">
              <input class="btn btn-active btn-lethal js-btn-lethal" type="submit" name="lethal" value="必殺!!">
            </form>
            <img class="img" src="<?php echo $_SESSION['fighter']->getImgFace(); ?>" alt="">
          </div>
          <!-- 操作キャラのステータス -->
          <div class="fighter-info-status">
            <div class="about">
              <div class="fighter">
                <p class="nickname"><?php echo $_SESSION['fighter']->getNickname(); ?></p>
                <h2 class="name"><?php echo $_SESSION['fighter']->getName(); ?></h2>
              </div>
              <div class="record">
                <p class="number"><?php echo $_SESSION['monsterNum']; ?><span class="unit">人</span></p>
                <span class="index">残る強敵</span>
              </div>
            </div>

            <!-- 操作キャラのHPゲージ -->
            <div class="parameter">
              <p class="index">体力</p>
              <div class="gauge">
                <p class="number">
                  <span id="js-hp-remain"><?php echo $_SESSION['fighter']->getHp(); ?></span>/<span id="js-hp-max"><?php echo $_SESSION['fighterMaxHp']; ?></span>
                <p>
                <div class="gauge-remain gauge-remain-hp js-gauge-hp">
                </div>
              </div>
            </div>
            <!-- 操作キャラのMPゲージ -->
            <div class="parameter">
              <span class="index">気力</span>
              <div class="gauge ">
                <p class="number">
                  <span id="js-mp-remain"><?php echo $_SESSION['fighter']->getMp(); ?></span>/<span id="js-mp-max">100</span>
                <p>
                  <div class="gauge-remain gauge-remain-mp js-gauge-mp">
                </div>
              </div>
            </div>
        </section>

        <!-- 3. コマンドエリア -->
        <section class="command-area">
          <form class="btn-command-wrapper" method="post">
            <input class="btn btn-active btn-command" type="submit" name="attack" value="攻撃">
            <input class="btn btn-active btn-command" type="submit" name="guard" value="防御">
            <input class="btn btn-active btn-command" type="submit" name="escape" value="逃げる">
          </form>
        </section>

    <?php } ?>

  <footer class="footer">
    <form method="post" action="">
      <input class="link_start" type="submit" name="reset" value="<< スタート画面に戻る">
    </form>
    <a class="link_anime" href="http://baki-anime.jp/" target=”_blank”>TVアニメ「バキ」公式サイトへッ!!</a>
  </footer>

  </main>
  <script
    src="//code.jquery.com/jquery-3.3.1.js"
    integrity="sha256-2Kok7MbOyxpgUVvAk/HJ2jigOSYS2auK4Pfzbm7uH60="
    crossorigin="anonymous"></script>
  <script src="js/app.js"></script>
  </body>
</html>
