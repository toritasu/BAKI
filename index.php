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
    <main class="container">
    <?php if(empty($_SESSION)){ ?>
        <header class="header">
          <img class="logo" src="img/logo.png" alt="刃牙ロゴ">
          <h1 class="title">地下格闘場戦士 VS 最凶死刑囚</h1>
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
            <img class="character-face js-character-face" src="<?php echo $fighters[0]->getImgFace(); ?>" alt="">
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
            <input class="btn btn-inactive js-btn-prohibit" type="submit" name="start" value="選べッ!!" disabled="disabled">
          </form>
        </section>

    <?php } else { ?>
      <!-- バトル画面 -->
      <!-- 0. カットイン -->
      <section class="cutin <?php if(getSessionFlash('encountFlg')) echo 'show'; ?>">
        <div class="cutin-enemy">
          <img class="img" src="<?php echo $_SESSION['monster']->getImgFace(); ?>" alt="<?php echo $_SESSION['monster']->getName(); ?>">
          <div class="telop">
            <p class="nickname"><?php echo $_SESSION['monster']->getNickname(); ?></p>
            <h2 class="name"><?php echo $_SESSION['monster']->getName(); ?></h2>
          </div>
        </div>
      </section>

      <!-- 1. 敵キャラの情報とヒストリーウィンドウ -->
      <section class="battle-window">
        <!-- 敵キャラのHPゲージ -->
        <ul class="enemy-info">
          <li class="name"><?php echo $_SESSION['monster']->getName(); ?></li>
          <li class="hp"><?php echo $_SESSION['monster']->getHp().'/'.$_SESSION['monsterMaxHp']; ?></li>
        </ul>
        <div class="gauge ">
          <div class="gauge gauge-remain gauge-remain-hp js-gauge-hp-enemy"></div>
        </div>
        <!-- 敵キャラの画像 -->
        <img class="enemy-img" src="<?php echo $_SESSION['monster']->getImg(); ?>" style="">
        <!-- History -->
        <div class="history">
          <p class="history-text"><?php echo (!empty($_SESSION['history'])) ? $_SESSION['history'] : ''; ?></p>
        </div>
      </section>

      <!-- 2. 操作キャラのステータス -->
      <section class="status-window">
        <!-- 操作キャラの画像 -->
        <div class="fighter-face">
          <img class="face-img" src="<?php echo $_SESSION['fighter']->getImgFace(); ?>" alt="">
        </div>
        <!-- 操作キャラのステータス -->
        <div class="fighter-status">
          <div class="">
            <p class="nickname"><?php echo $_SESSION['fighter']->getNickname(); ?></p>
            <h2 class="name"><?php echo $_SESSION['fighter']->getName(); ?></h2>
          </div>
          <div class="">
            <p class="">残る強敵</p>
            <p class="number"><?php echo $_SESSION['monsterNum']; ?>人</p>
          </div>
          <!-- 操作キャラのHPゲージ -->
          <div class="gauge ">
            <div class="gauge gauge-remain gauge-remain-hp js-gauge-hp">
              <span class="number hp"><?php echo $_SESSION['fighter']->getHp().'/'.$_SESSION['fighterMaxHp']; ?></span>
            </div>
          <!-- 操作キャラの気力ゲージ -->
          </div>
          <div class="gauge ">
            <div class="gauge gauge-remain gauge-remain-rethal js-gauge-rethal">
              <span class="number mp"><?php echo $_SESSION['fighter']->getHp().'/'.$_SESSION['fighterMaxHp']; ?></span>
            </div>
          </div>
          <div class="gauge_num"></div>
          <div class="gauge_bg"><div class="gauge_main" style="width: 100%;"></div></div>
        </div>
      </section>

      <!-- 3. コマンドエリア -->
      <section class="command-area">
        <form class="form" method="post">
          <input class="btn btn-active" type="submit" name="attack" value="攻撃ッ!!">
          <input class="btn btn-active" type="submit" name="guard" value="防御ッ!!">
          <input class="btn btn-active" type="submit" name="escape" value="退却ッ!!">
        </form>
      </section>

    <?php } ?>

  <footer class="footer">
    <?php if(!empty($_SESSION)){ ?>
    <form method="post" action="">
      <input class="link_start" type="submit" name="reset" value="<< スタート画面へ">
    </form>
    <?php } ?>
    <a class="link_anime" href="http://baki-anime.jp/" target=”_blank”>TVアニメ「バキ」公式サイトへッ!!</a>
  </footer>

  </main>
  <script
    src="http://code.jquery.com/jquery-3.3.1.js"
    integrity="sha256-2Kok7MbOyxpgUVvAk/HJ2jigOSYS2auK4Pfzbm7uH60="
    crossorigin="anonymous"></script>
  <script src="js/app.js"></script>
  </body>
</html>
